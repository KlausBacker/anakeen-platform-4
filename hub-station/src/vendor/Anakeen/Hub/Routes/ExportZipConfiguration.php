<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\ContextManager;
use Anakeen\Core\ExportCollection;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Exception;
use Anakeen\Hub\Exchange\HubExportInstance;
use Anakeen\Router\ApiV2Response;
use Anakeen\Search\Internal\SearchSmartData;
use Anakeen\SmartElementManager;
use SmartStructure\Fields\Hubconfiguration as Fields;

/**
 *
 * @note used by route /hub/config/{hubId}.zip
 */
class ExportZipConfiguration extends ExportConfiguration
{
    const PREIMPORT = "preImport";
    protected $hubName = "";
    /**
     * @var $hubInstance \SmartStructure\Hubinstanciation
     */
    protected $hubInstance;
    protected $hasSuperRole;
    protected $tmpDir;
    /**
     * @var array
     */
    protected $extraIds = [];

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->hubName = $args["hubId"];

        $this->hubInstance = SmartElementManager::getDocument($this->hubName);

        $outputFile = $this->exportMain();

        $response = ApiV2Response::withFile($response, $outputFile, sprintf("%s.zip", $this->hubInstance->getTitle()), false);
        unlink($outputFile);
        return $response;
    }


    protected function exportMain()
    {
        $outFile = $this->getTmpDir() . "hubConfig";


        $hubExport = new HubExportInstance($this->hubInstance);
        $hubExport->getZip($outFile);

        return $outFile;
    }




    protected function getTmpDir()
    {
        if (!$this->tmpDir) {
            $this->tmpDir = tempnam(ContextManager::getTmpDir(), "hub");
            if (file_exists($this->tmpDir)) {
                unlink($this->tmpDir);
            }
            mkdir($this->tmpDir);
            $this->tmpDir .= "/";
        }
        return $this->tmpDir;
    }
}
