<?php
namespace Anakeen\Routes\Devel\Import;

use Anakeen\Core\Internal\ImportSmartConfiguration;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class Configuration
 * @package Anakeen\Routes\Devel\Import
 * @note    Used by route : POST /api/v2/devel/import/configuration/
 */
class Configuration
{
    protected $verbose = false;
    protected $dryRun = false;

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $verbose = $request->getParam("verbose", false);
        $this->verbose = ($verbose == "true" || $verbose == "yes" || $verbose == "1");

        $dryRun = $request->getParam("dryRun", false);
        $this->dryRun = ($dryRun == "true" || $dryRun == "yes" || $dryRun == "1");
    }

    protected function doRequest(\Slim\Http\request $request)
    {
        $files = $request->getUploadedFiles();

        if (empty($files) || count($files) === 0) {
            $e = new Exception('DEV0102');
            $e->setHttpStatus('400', 'Bad request');
            throw $e;
        }

        $import = new ImportSmartConfiguration();
        $import->setVerbose($this->verbose);
        $import->setOnlyAnalyze($this->dryRun);
        foreach ($files as $currentFile) {
            $import->import($currentFile->file);
        }

        $err = $import->getErrorMessage();

        if ($err) {
            $e = new Exception('DEV0103', $err);
            $e->setHttpStatus('500', 'Internal error');
            throw $e;
        }
        return ["Import configuration done"];
    }

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withMessages($response, $this->doRequest($request));
    }
}
