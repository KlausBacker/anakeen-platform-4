<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Router\ApiV2Response;

class MainConfiguration extends \Anakeen\Components\Grid\Routes\GridContent
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $search = new \SearchDoc("", "HUBCONFIGURATION");
        $search->setObjectReturn(true);
        $search->overrideViewControl();
        $search->search();
        $hubConfigurations = $search->getDocumentList();
        $return = [];
        /**
         * @var $hubConfig \SmartStructure\Hubconfiguration
         */
        foreach ($hubConfigurations as $hubConfig) {
            $return[] = $hubConfig->getConfiguration();
        }
        return ApiV2Response::withData($response, $return);
    }
}