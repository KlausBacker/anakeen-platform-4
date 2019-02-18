<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Core\VaultManager;
use Anakeen\Router\Exception;

class HubFavIcon
{
    protected $structureId = "";

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     * @throws Exception
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->structureId = $args["favIconURL"];
        $doc = SEManager::getDocument($this->structureId);
        if ($doc) {
            $image = $doc->getFileInfo($doc->getAttributeValue("hub_instanciation_icone"));
            $file = VaultManager::getFileInfo($image["id_file"]);
            if (!empty($file) && isset($file->path)) {
                return ApiV2Response::withFile($response, $file->path);
            }
        }
        return $response;
    }
}
