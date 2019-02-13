<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use SmartStructure\Fields\Hubconfiguration as Fields;

class MainConfiguration extends \Anakeen\Components\Grid\Routes\GridContent
{
    protected $structureName = "";

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Database\Exception
     * @throws \Anakeen\Search\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->structureName = $args["hubId"];
        $search = new \Anakeen\Search\Internal\SearchSmartData("", "HUBCONFIGURATION");
        $search->setObjectReturn(true);
        $search->overrideViewControl();
        if (!intval($this->structureName)) {
            $this->structureName = SEManager::getIdFromName($this->structureName);
        }
        $search->addFilter("%s = '%s'", Fields::hub_station_id, $this->structureName);
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
