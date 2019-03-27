<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\SEManager;

class HubElementStructures
{
    protected $structureId = "";

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $structures = array_merge(SEManager::getFamily("HUBCONFIGURATIONSLOT")->getChildFam(), SEManager::getFamily("HUBCONFIGURATIONVUE")->getChildFam());

        $data=[];
        foreach ($structures as $structure) {
            $data[]=[
                "value" =>  $structure["id"],
                "text" => $structure["title"]
            ];
        }

        return $response->withJson($data);
    }
}
