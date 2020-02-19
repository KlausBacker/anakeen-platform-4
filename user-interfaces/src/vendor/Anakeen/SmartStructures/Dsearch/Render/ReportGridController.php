<?php

namespace Anakeen\SmartStructures\Dsearch\Render;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\SmartGridConfigBuilder;

class ReportGridController extends DefaultGridController
{
    public static function getGridConfig($collectionId, $clientConfig)
    {
        $configBuilder = new SmartGridConfigBuilder();
        if (isset($collectionId)) {
            $configBuilder->setCollection($collectionId);
        }
        if (isset($clientConfig["pageable"])) {
            $configBuilder->setPageable($clientConfig["pageable"]);
        }
        if (isset($clientConfig["columns"])) {
            $configBuilder->setColumns($clientConfig["columns"]);
        } else {
            // use default columns for the collection
            $configBuilder->useDefaultColumns();
        }
        if (isset($clientConfig["actions"])) {
            foreach ($clientConfig["actions"] as $action) {
                $configBuilder->addRowAction($action);
            }
        }
        $configBuilder->addRowAction(array("action"=> "display", "title"=> "Display"));
        $config = $configBuilder->getConfig();
        return $config;
    }
}
