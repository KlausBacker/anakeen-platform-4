<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;
use Anakeen\Core\SEManager;

class ElementsGridController extends DefaultGridController
{
    public static function getGridConfig($collectionId, $clientConfig)
    {
        $configBuilder = new SmartGridConfigBuilder();
        if (isset($clientConfig["pageable"])) {
            $configBuilder->setPageable($clientConfig["pageable"]);
        }
        $configBuilder->addProperty("title");
        $configBuilder->addProperty("name", array("title"=>"Logical name"));
        $configBuilder->addProperty("fromid", array("relation"=>"-1","smartType"=>"text","title"=>"Type"));
        $configBuilder->addProperty("initid", array("filterable"=>self::getFilterable("int"),"title"=>"InitId","smartType"=>"integer"));
        $configBuilder->addProperty("doctype", array("hidden"=>true));
        $configBuilder->addProperty("profid", array("hidden"=>true));
        $configBuilder->addRowAction(array("action"=>"consult", "title"=>"Consult"));
        $configBuilder->addRowAction(array("action"=>"viewJSON", "title"=>"JSON"));
        $configBuilder->addRowAction(array("action"=>"viewXML", "title"=>"XML"));
        $configBuilder->addRowAction(array("action"=>"viewProps", "title"=>"Properties"));
        $configBuilder->addRowAction(array("action"=>"security", "title"=>"Security"));
        return $configBuilder->getConfig();
    }

    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();
        // Searching in all Smart Structures
        $contentBuilder->setCollection(0);
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
        if (isset($clientConfig["filter"])) {
            $contentBuilder->addFilter($clientConfig["filter"]);
        }
        $contentBuilder->addProperty("title");
        $contentBuilder->addProperty("name");
        $contentBuilder->addProperty("fromid");
        $contentBuilder->addProperty("initid");
        $contentBuilder->addProperty("doctype");
        $contentBuilder->addProperty("profid");
        $fullContent = $contentBuilder->getContent();
        $content = $fullContent["content"];
        foreach ($content as $key => $val) {
            $fullContent["content"][$key]["properties"]["fromid"] = SEManager::getNameFromId($val["properties"]["fromid"]);
        }
        return $fullContent;
    }

    protected static function getFilterable($type)
    {
        $operators = Operators::getTypeOperators($type);

        if (!$operators) {
            return false;
        }

        $stringsOperators=[];
        foreach ($operators as $k => $operator) {
            if (!empty($operator["typedLabels"])) {
                $stringsOperators[$k] = $operator["typedLabels"][$type] ?? $operator["label"];
            } else {
                $stringsOperators[$k] = $operator["label"];
            }
        }


        return [
            "operators" => [
                "string" => $stringsOperators,
                "date" => $stringsOperators,
            ],
            "cell" => [
                "enable" => true,
                "delay" => 9999999999 // Wait 115 days : only way to have the clear button easyly
            ]
        ];
    }
}
