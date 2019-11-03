<?php


namespace Anakeen\Core\Internal;

use Anakeen\Core\Exception;
use Anakeen\Core\SmartStructure\ExportConfiguration;
use Anakeen\Core\Utils\Xml;
use Anakeen\Exchange\ExportAccounts;

class ImportAnyConfiguration
{
    const SMARTCONFIG = "SmartConfig";
    const ACCOUNTCONFIG = "AccountConfig";
    const SMARTELEMENTCONFIG = "SmartElementConfig";
    protected $verbose = false;
    protected $dryRun = false;
    /**
     * @var \DOMDocument
     */

    protected $dom;
    protected $filepath = '';
    /**
     * @var array
     */
    protected $debugData;
    /**
     * @var array
     */
    protected $verboseMessages = [];


    private $importSmartObject;


    public function load($xmlFile)
    {
        $error = self::checkValidity($xmlFile);

        if ($error) {
            throw new Exception($error);
        }

        $this->dom = new \DOMDocument();
        $this->dom->load($xmlFile);

        $this->filepath = $xmlFile;
    }


    public function getImportType()
    {
        if (Xml::getPrefix($this->dom, ExportConfiguration::NSURL)) {
            return self::SMARTCONFIG;
        } elseif (Xml::getPrefix($this->dom, ExportAccounts::NSURI)) {
            return self::ACCOUNTCONFIG;
        } elseif ($this->dom->documentElement->tagName === "documents") {
            return self::SMARTELEMENTCONFIG;
        }

        return false;
    }

    /**
     * @throws Exception
     * @throws \Anakeen\Exception
     */
    public function import()
    {
        switch ($this->getImportType()) {
            case self::SMARTCONFIG:
                /** ============================================================
                 * IMPORT Smart XML like routes, profile, structure, render config
                 * ============================================================= */
                $this->importAsSmartConfiguration($this->filepath);
                break;
            case self::ACCOUNTCONFIG:
                /** ============================================================
                 * IMPORT Account : user , role and group
                 * ============================================================= */
                $this->importAsAccountsConfiguration($this->filepath);
                break;
            case self::SMARTELEMENTCONFIG:
                /** ============================================================
                 * IMPORT Smart Element Data
                 * ============================================================= */
                $this->importAsSmartElementDataConfiguration($this->filepath);
                break;
        }
    }


    public static function checkValidity($xmlFile)
    {
        $dom = new \DOMDocument();
        if (!@$dom->load($xmlFile)) {
            return sprintf('Configuration file "%s" is not an xml file', $xmlFile);
        }

        if (!Xml::getPrefix($dom, ExportConfiguration::NSURL) &&
            !Xml::getPrefix(
                $dom,
                ExportAccounts::NSURI
            ) &&
            ($dom->documentElement->tagName !== "documents")) {
            return sprintf('File "%s" is not detected has a configuration file', $xmlFile);
        }
        return "";
    }

    /**
     * @param bool $verbose
     */
    public function setVerbose(bool $verbose): void
    {
        $this->verbose = $verbose;
    }

    /**
     * @param bool $dryRun
     */
    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }



    /**
     * @return array
     */
    public function getDebugData(): array
    {
        return $this->debugData;
    }

    protected function importAsSmartConfiguration($configFile)
    {
        $oImport = $this->getImportSmartObject();
        $oImport->clearVerboseMessages();
        $oImport->importAll($configFile);

        if ($oImport->getErrorMessage()) {
            throw new Exception($oImport->getErrorMessage());
        }

        $this->debugData = $oImport->getDebugData();
        $this->verboseMessages = $oImport->getVerboseMessages();
    }

    protected function importAsAccountsConfiguration($accountsFile)
    {
        $import = new \Anakeen\Exchange\ImportAccounts();
        $import->setFile($accountsFile);
        $import->setAnalyzeOnly($this->dryRun);
        $import->import();

        if ($import->hasErrors()) {
            throw new \Anakeen\Exception(implode("\n", $import->getErrors()));
        }

        $this->debugData = $import->getReport();
        foreach ($this->debugData as $debugDatum) {
            $this->verboseMessages[] = sprintf("%s : %s", $debugDatum["action"], $debugDatum["login"]);
        }
    }


    /**
     * @return array
     */
    public function getVerboseMessages(): array
    {
        return $this->verboseMessages;
    }

    public function clearVerboseMessages()
    {
         $this->verboseMessages=[];
        $this->debugData=[];
    }

    protected function importAsSmartElementDataConfiguration($xmlData)
    {
        $iXml = new \Anakeen\Exchange\ImportXml();
        $iXml->analyzeOnly($this->dryRun);
        $this->debugData = $iXml->importSingleXmlFile($xmlData);
        $errors = [];
        foreach ($this->debugData as $debugDatum) {
            if ($debugDatum["err"]) {
                $errors[] = $debugDatum["err"];
            }
            $this->verboseMessages[] = sprintf("%s : %s", $debugDatum["msg"], $debugDatum["title"]);
        }
        if ($errors) {
            throw new \Anakeen\Exception(implode("\n", $errors));
        }
    }

    private function getImportSmartObject()
    {
        if (!$this->importSmartObject) {
            $hasWorkflow = class_exists(\Anakeen\Workflow\ImportWorkflowConfiguration::class);
            $hasUi = class_exists(\Anakeen\Ui\ImportRenderConfiguration::class);

            if ($hasUi) {
                if ($hasWorkflow) {
                    $this->importSmartObject = new \Anakeen\Workflow\ImportWorkflowConfiguration();
                } else {
                    $this->importSmartObject = new \Anakeen\Ui\ImportRenderConfiguration();
                }
            } else {
                $this->importSmartObject = new \Anakeen\Core\Internal\ImportSmartConfiguration();
            }

            $this->importSmartObject->setOnlyAnalyze($this->dryRun);
            $this->importSmartObject->setVerbose(true);
        }
        return $this->importSmartObject;
    }
}
