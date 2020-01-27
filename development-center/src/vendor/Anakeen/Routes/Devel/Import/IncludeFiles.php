<?php

namespace Anakeen\Routes\Devel\Import;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\ImportAnyConfiguration;
use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;
use Control\Internal\Context;
use LibSystem;

class IncludeFiles extends ImportAnyConfiguration
{
    protected $import;
    protected $verbose = false;
    protected $dryRun = false;
    protected $fileName = "";

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withMessages($response, $this->doRequest($request));
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $verbose = $request->getParam("verbose", false);
        $this->verbose = ($verbose == "true" || $verbose == "yes" || $verbose == "1");

        $dryRun = $request->getParam("dryRun", false);
        $this->dryRun = ($dryRun == "true" || $dryRun == "yes" || $dryRun == "1");
    }

    public function doRequest(\Slim\Http\request $request)
    {
        $this->fileName = LibSystem::tempnam(null, "ank-app-download");
        $body = $request->getBody();
        $tmp = fopen($this->fileName, "w+");
        while (!$body->eof()) {
            fputs($tmp, $body->read(2048));
        }
        fclose($tmp);

        $this->import = new ImportAnyConfiguration();
        $this->import->setVerbose($this->verbose);
        $this->import->setDryRun($this->dryRun);

        $zip = new \ZipArchive();
        $files = $zip->open($this->fileName);
        if (!$files) {
            throw new Exception("Cannot open zip file %s", $this->fileName);
        } else {
            $fileArray = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filePath = $zip->getNameIndex($i);
                $fileInfo = pathinfo($filePath);
                if (isset($fileInfo["extension"])) {
                    $contextPath = ContextManager::getRootDirectory();
                    $dirPath = sprintf("%s/%s%s", $contextPath, "vendor/Anakeen", dirname($filePath));
                    if (!$this->dryRun) {
                        if (!is_dir($dirPath)) {
                            if (mkdir($dirPath, 0755, true) === false) {
                                throw new \RuntimeException(sprintf("Error: could not create %s directory", $dirPath));
                            }
                        }
                    }
                    $fp = $zip->getStream($filePath);
                    $content = '';
                    while (!feof($fp)) {
                        $content .= fread($fp, 8192);
                    }
                    fclose($fp);
                    $fileName = sprintf("%s/%s", $dirPath, basename($filePath));
                    if (!$this->dryRun) {
                        file_put_contents($fileName, $content);
                    }
                    array_push($fileArray, sprintf("import file: %s ", $fileName));
                }
            }
            $zip->close();
        }
        return $fileArray;
    }
}
