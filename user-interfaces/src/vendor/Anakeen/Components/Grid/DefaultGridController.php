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
        $contentBuilder = new SmartGridContentBuilder("DEVBILL");
        $contentBuilder
        ->addProperty("title")
        ->addField("bill_title")
        ->addField("bill_author")
        ->addAbstract("my_custom_column", function ($smartElement) {
            return $smartElement->getTitle();
        });
        return $contentBuilder->getContent();
    }

    public static function exportGridContent($collectionId, $clientConfig)
    {
    }
}
