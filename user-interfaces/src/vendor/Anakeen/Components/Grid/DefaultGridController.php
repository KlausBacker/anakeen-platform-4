<?php

namespace Anakeen\Components\Grid;

class DefaultGridController implements SmartGridController
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
        $config = $configBuilder->getConfig();
        return $config;
    }

    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();
        if (isset($collectionId)) {
            $contentBuilder->setCollection($collectionId);
        }
        if (isset($clientConfig["pageable"]["pageSize"])) {
            $contentBuilder->setPageSize($clientConfig["pageable"]["pageSize"]);
        }
        if (isset($clientConfig["page"])) {
            $contentBuilder->setPage($clientConfig["page"]);
        }
        if (isset($clientConfig["columns"])) {
            foreach ($clientConfig["columns"] as $column) {
                $contentBuilder->addColumn($column);
            }
        }
        if (isset($clientConfig["sort"])) {
            foreach ($clientConfig["sort"] as $sort) {
                $contentBuilder->addSort($sort["field"], $sort["dir"]);
            }
        }
        return $contentBuilder->getContent();
    }

    public static function exportGridContent($collectionId, $clientConfig)
    {
    }
}
