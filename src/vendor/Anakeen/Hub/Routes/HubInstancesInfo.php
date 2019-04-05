<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Search\SearchElements;

class HubInstancesInfo
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
        $se=new SearchElements("HUBINSTANCIATION");
        $dl=$se->search()->getResults();
        $data=[];
        foreach ($dl as $instance) {
            $data[]=[
                "value" =>  $instance->initid,
                "text" => $instance->getTitle()?:$instance->initid,
                "icon" => $instance->getIcon()
            ];
        }

        return $response->withJson($data);
    }
}
