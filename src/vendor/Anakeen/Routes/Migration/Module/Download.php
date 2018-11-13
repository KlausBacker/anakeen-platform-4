<?php

namespace Anakeen\Routes\Migration\Module;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Config\RouterInfo;
use Anakeen\Router\Exception;
use Anakeen\Router\ExportRoutesConfiguration;
use Anakeen\Router\RouterManager;
use Anakeen\Search\SearchElements;
use Anakeen\Ui\ExportRenderAccessConfiguration;
use Anakeen\Workflow\ExportElementConfiguration;

class Download
{
    protected $tmpDir;
    /**
     * @var \ZipArchive $zip ;
     */
    protected $zip;
    protected $vendor;
    protected $outputFile;
    protected $outputPath;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);
        $data = $this->doRequest();

        return ApiV2Response::withData($response, $data);
        //return ApiV2Response::withFile($response, $data);
    }

    protected function initParameters($args)
    {
        $this->vendor = $args["vendor"];


        $this->outputPath = sprintf("vendor/%s", $this->vendor);
        $moduleName = ContextParameterManager::getValue("Migration", "MODULE");
        if ($moduleName) {
            $this->outputPath .= "/" . $moduleName;
        }
    }

    protected function doRequest()
    {
        $this->zip = new \ZipArchive();
        $this->outputFile = sprintf("%s/%s.zip", ContextManager::getTmpDir(), $this->vendor);
        $ret = $this->zip->open($this->outputFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($ret !== true) {
            throw new Exception(sprintf("Cannot create zip file \"%s\" %d", $this->outputFile, $ret));
        } else {
            $path = sprintf("%s/%s", ContextManager::getRootDirectory(), $this->outputPath);

            $this->zipAddDirectory($path);
            $this->addStructuresConfig();
            $this->addRoutesConfig();
            $this->zip->close();
        }

        return $this->outputFile;
    }

    protected function addStructuresConfig()
    {
        $s = new SearchElements("-1");
        $s->addFilter("atags ->> 'vendor' = '%s'", $this->vendor);
        $structures = $s->search()->getResults();
        /** @var SmartStructure $structure */
        foreach ($structures as $structure) {
            $this->addStructureConfig($structure);
        }
    }


    protected function addStructureConfig(SmartStructure $structure)
    {
        $structName = self::camelCase($structure->name);
        $e = new ExportRenderAccessConfiguration($structure);
        $e->extractCvRef();
        $e->extractProfil("ref");
        $e->insertStructConfig();
        $xmlFile = sprintf("%s/SmartStructures/%s/500-%sSetting.xml", $this->outputPath, $structName, $structName);
        $this->zip->addFromString($xmlFile, $e->toXml());


        $e = new ExportRenderAccessConfiguration($structure);
        $e->extractProfil("access");
        $e->extractCvAccess();
        $xmlFile = sprintf("%s/SmartStructures/%s/Settings/240-%sProfiles.xml", $this->outputPath, $structName, $structName);
        $this->zip->addFromString($xmlFile, $e->toXml());




        $e = new SmartStructure\ExportConfiguration($structure);
        $e->extractProps();
        $e->extractFields();
        $e->extractHooks();
        $e->extractAutoComplete();
        $e->extractDefaults();
        $e->insertStructConfig();
        $xmlFile = sprintf("%s/SmartStructures/%s/100-%sStructure.xml", $this->outputPath, $structName, $structName);
        $this->zip->addFromString($xmlFile, $e->toXml());


        $e = new SmartStructure\ExportConfiguration($structure);
        $e->extractParameters();
        $e->insertStructConfig();
        $xmlFile = sprintf("%s/SmartStructures/%s/110-%sParameters.xml", $this->outputPath, $structName, $structName);
        $this->zip->addFromString($xmlFile, $e->toXml());

        $this->addTimersConfig($structure);
        $this->addMasksConfig($structure);
        $this->addMailTemplatesConfig($structure);
        $this->addProfilesConfig($structure);
        $this->addCvdocsConfig($structure);
        $this->addFieldAccessConfig($structure);
    }

    protected function addTimersConfig(SmartStructure $structure)
    {
        $structName = self::camelCase($structure->name);
        $s = new SearchElements("TIMER");
        $s->addFilter("%s = '%d'", \SmartStructure\Fields\Timer::tm_family, $structure->id);
        $timers = $s->search()->getResults();
        foreach ($timers as $timer) {
            $xml = ExportElementConfiguration::getTimerConfig($timer->id);
            $xmlFile = sprintf("%s/SmartStructures/%s/Settings/Timers/260-Timer%s.xml", $this->outputPath, $structName, self::getLogicalName($timer));
            $this->zip->addFromString($xmlFile, $xml);
        }
    }

    protected function addMasksConfig(SmartStructure $structure)
    {
        $structName = self::camelCase($structure->name);
        $s = new SearchElements("MASK");
        $s->addFilter("%s = '%d'", \SmartStructure\Fields\Mask::msk_famid, $structure->id);
        $masks = $s->search()->getResults();
        foreach ($masks as $mask) {
            $xml = ExportElementConfiguration::getMaskConfig($mask->id);
            $xmlFile = sprintf("%s/SmartStructures/%s/Settings/Masks/210-Mask%s.xml", $this->outputPath, $structName, self::getLogicalName($mask));
            $this->zip->addFromString($xmlFile, $xml);
        }
    }
    protected function addMailTemplatesConfig(SmartStructure $structure)
    {
        $structName = self::camelCase($structure->name);
        $s = new SearchElements("MAILTEMPLATE");
        $s->addFilter("%s = '%d'", \SmartStructure\Fields\Mailtemplate::tmail_family, $structure->id);
        $mails = $s->search()->getResults();
        foreach ($mails as $mail) {
            $xml = ExportElementConfiguration::getMailTemplateConfig($mail->id);
            $xmlFile = sprintf("%s/SmartStructures/%s/Settings/MailTemplates/250-MailTemplate%s.xml", $this->outputPath, $structName, self::getLogicalName($mail));
            $this->zip->addFromString($xmlFile, $xml);
        }
    }
    protected function addCvdocsConfig(SmartStructure $structure)
    {
        $structName = self::camelCase($structure->name);
        $s = new SearchElements("CVDOC");
        $s->addFilter("%s = '%d'", \SmartStructure\Fields\Cvdoc::cv_famid, $structure->id);
        $cvs = $s->search()->getResults();
        foreach ($cvs as $cv) {
            $xml = ExportElementConfiguration::getCvdocConfig($cv->id);
            $xmlFile = sprintf("%s/SmartStructures/%s/Settings/ViewControls/220-ViewControl%s.xml", $this->outputPath, $structName, self::getLogicalName($cv));
            $this->zip->addFromString($xmlFile, $xml);
        }
    }

    protected function addProfilesConfig(SmartStructure $structure)
    {
        $structName = self::camelCase($structure->name);
        $s = new SearchElements("PDOC");
        $s->addFilter("%s = '%d'", \SmartStructure\Fields\Pdoc::dpdoc_famid, $structure->id);
        $profiles = $s->search()->getResults();
        foreach ($profiles as $profile) {
            $xml = ExportElementConfiguration::getProfileConfig($profile->id);
            $xmlFile = sprintf("%s/SmartStructures/%s/Settings/Profiles/240-Profile%s.xml", $this->outputPath, $structName, self::getLogicalName($profile));
            $this->zip->addFromString($xmlFile, $xml);
        }
    }


    protected function addFieldAccessConfig(SmartStructure $structure)
    {
        $structName = self::camelCase($structure->name);
        $s = new SearchElements("FIELDACCESSLAYERLIST");
        $s->addFilter("%s = '%d'", \SmartStructure\Fields\Fieldaccesslayerlist::fall_famid, $structure->id);
        $profiles = $s->search()->getResults();
        foreach ($profiles as $profile) {
            $xml = ExportElementConfiguration::getFieldAccessConfig($profile->id);
            $xmlFile = sprintf("%s/SmartStructures/%s/Settings/Profiles/270-FieldAccesses%s.xml", $this->outputPath, $structName, self::getLogicalName($profile));
            $this->zip->addFromString($xmlFile, $xml);
        }
    }

    protected static function getLogicalName(SmartElement $e)
    {
        if ($e->name) {
            return $e->name;
        } else {
            return sprintf("Name{%d}", $e->id);
        }
    }

    protected static function camelCase($s)
    {
        return ucfirst(strtolower($s));
    }

    protected function addRoutesConfig()
    {
        $routes = RouterManager::getRoutes();
        $pattern = sprintf("/^%s::/i", $this->vendor);
        $matchRoutes = [];
        foreach ($routes as $route) {
            if (preg_match($pattern, $route->name)) {
                $info = new RouterInfo($route);
                $matchRoutes[] = $info;
            }
        }

        $e = new ExportRoutesConfiguration();
        $e->extractRoutes($matchRoutes);
        $xmlFile = sprintf("%s/Config/Routes/110-%sRoutes.xml", $this->outputPath, $this->vendor);
        $this->zip->addFromString($xmlFile, $e->toXml());


        $e = new ExportRoutesConfiguration();
        $e->extractRouteAcl($this->vendor);
        $xmlFile = sprintf("%s/Config/Routes/120-%sAccess.xml", $this->outputPath, $this->vendor);
        $this->zip->addFromString($xmlFile, $e->toXml());
    }

    protected function zipAddDirectory($source, $pattern = "*.{php,xml}")
    {
        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                    continue;
                }
                $file = realpath($file);
                if (is_dir($file) === true) {
                    $absPattern = sprintf("%s/%s", $file, $pattern);
                    $options = array('remove_path' => ContextManager::getRootDirectory());
                    $this->zip->addGlob($absPattern, GLOB_BRACE, $options);
                }
            }
        }
    }
}
