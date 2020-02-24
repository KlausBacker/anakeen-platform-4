<?php

namespace Anakeen\SmartStructures\Dsearch\Render;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;

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
        $configBuilder->useDefaultColumns();
        $configBuilder->addRowAction(array("action"=> "display", "title"=> "Display"));
        $config = $configBuilder->getConfig();
        return $config;
    }
    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();
        $config = self::getGridConfig($collectionId, $clientConfig);
        foreach ($config["columns"] as $column) {
            $contentBuilder->addColumn($column);
        }
        if (isset($collectionId)) {
            $contentBuilder->setCollection($collectionId);
        }
        if (isset($clientConfig["pageable"]["pageSize"])) {
            $contentBuilder->setPageSize($clientConfig["pageable"]["pageSize"]);
        }
        if (isset($clientConfig["page"])) {
            $contentBuilder->setPage($clientConfig["page"]);
        }

        if (isset($clientConfig["filter"])) {
            $filterLogic = $clientConfig["filter"]["logic"];
            $filters = $clientConfig["filter"]["filters"];
            $contentBuilder->addFilter($clientConfig["filter"]);
        }
        if (isset($clientConfig["sort"])) {
            foreach ($clientConfig["sort"] as $sort) {
                $contentBuilder->addSort($sort["field"], $sort["dir"]);
            }
        }
        return $contentBuilder->getContent();
    }
}
