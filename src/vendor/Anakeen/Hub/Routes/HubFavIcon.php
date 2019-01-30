<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Dcp\VaultManager;

class HubFavIcon
{
    protected $structureId = "";

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return ApiV2Response
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Router\Exception
     * @throws \Dcp\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->structureId = $args["favIconURL"];
        $doc = SEManager::getDocument($this->structureId);
        $image = $doc->getFileInfo($doc->getAttributeValue("hub_instanciation_icone"));
        $file = VaultManager::getFileInfo($image["id_file"]);

        return ApiV2Response::withFile($response, $file->path);
    }
}
