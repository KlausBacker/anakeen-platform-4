<?php
namespace Anakeen\Routes\Devel\Import;

//use Anakeen\Core\Internal\ImportSmartConfiguration;
use Anakeen\Core\Internal\ImportAnyConfiguration;
use Anakeen\Core\Internal\ImportSmartConfiguration;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;

/**
 * Class Configuration
 * @package Anakeen\Routes\Devel\Import
 * @note    Used by route : POST /api/v2/devel/import/configuration/
 */
class Configuration
{
    protected $verbose = false;
    protected $dryRun = false;
    /** @var ImportAnyConfiguration */
    protected $import;

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $verbose = $request->getParam("verbose", false);
        $this->verbose = ($verbose == "true" || $verbose == "yes" || $verbose == "1");

        $dryRun = $request->getParam("dryRun", false);
        $this->dryRun = ($dryRun == "true" || $dryRun == "yes" || $dryRun == "1");
    }

    /**
     * @param $files
     * @throws Exception
     * @throws \Anakeen\Exception
     */
    protected function importFiles($files)
    {
        foreach ($files as $fileItem) {
            if (is_a($fileItem, \Slim\Http\UploadedFile::class)) {
                $this->import->load($fileItem->file);
                $this->import->import();
            } elseif (is_array($fileItem)) {
                $this->importFiles($fileItem);
            }
        }
    }

    protected function doRequest(\Slim\Http\request $request)
    {
        $files = $request->getUploadedFiles();

        if (empty($files) || count($files) === 0) {
            $e = new Exception('DEV0102');
            $e->setHttpStatus('400', 'Bad request');
            throw $e;
        }

        $this->import = new ImportAnyConfiguration();
        $this->import->setVerbose($this->verbose);
        $this->import->setDryRun($this->dryRun);

        try {
            $this->importFiles($files);
        } catch (Exception $e) {
            $e = new Exception('DEV0103', $e->getMessage());
            $e->setHttpStatus(400, 'Importation error');
            $e->setUserMessage($e->getMessage());
            throw $e;
        }
        return $this->import->getVerboseMessages();
    }

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        return ApiV2Response::withMessages($response, $this->doRequest($request));
    }
}
