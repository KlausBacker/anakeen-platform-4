<?php
namespace Anakeen\Routes\Core;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Dcp\VaultManager;

/**
 * Class RecordedFile
 *
 * Download temporary file
 * @note    Used by route : GET /api/v2/files/recorded/temporary/{file}[.{extension}]
 * @package Anakeen\Routes\Core
 */
class RecordedFile
{
    protected $extension;
    protected $fileId;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $fileInfo = VaultManager::getFileInfo($this->fileId);

        if (!$fileInfo) {
            $e = new Exception("CRUD0617", $this->fileId);
            $e->setHttpStatus(404, "File not found");
            throw $e;
        }

        if (!$fileInfo->id_tmp) {
            throw new Exception("CRUD0616");
        }

        return ApiV2Response::withFile($response, $fileInfo->path, $fileInfo->name, false);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->fileId=urldecode($args["file"]);
        if (!empty($args["extension"])) {
            $this->extension = $args["extension"];
        }
    }

}
