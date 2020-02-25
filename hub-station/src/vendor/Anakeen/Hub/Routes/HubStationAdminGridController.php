<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;
use SmartStructure\Fields\Hubconfiguration as Fields;
use Anakeen\Core\SEManager;
use function foo\func;

class HubStationAdminGridController extends DefaultGridController
{
    public static function getGridConfig($collectionId, $clientConfig)
    {
        $configBuilder = new SmartGridConfigBuilder();
        if (isset($clientConfig["pageable"])) {
            $configBuilder->setPageable($clientConfig["pageable"]);
        }
        $configBuilder->addAbstractColumn("key", array());
        $configBuilder->addProperty("title");
        $configBuilder->addField("hub_docker_position", array("abstract"=>true, "hidden"=>true), "HUBCONFIGURATION");
        $configBuilder->addField("hub_order", array(
            "field" => "hub_type",
            "smartType" => "text",
            "abstract" => true,
            "title" => "Type",
            "sortable" => true,
            "filterable" => self::getFilterable("text")
        ), "HUBCONFIGURATION");
        return $configBuilder->getConfig();
    }

    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();
        // Searching in all Smart Structures
        $contentBuilder->setCollection("HUBCONFIGURATION");
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
        $contentBuilder->getSearch()->setOrder(Fields::hub_order.' asc');
        $contentBuilder->getSearch()->addFilter("%s = '%s'", Fields::hub_station_id, SEManager::getDocument($collectionId)->initid);
        $contentBuilder->addProperty("title");
        $contentBuilder->addAbstract("hub_docker_position", function ($se) {
            return $se->hub_docker_position;
        });
        $contentBuilder->addAbstract("key", function ($se) {
            return "";
        });
        $contentBuilder->addAbstract("hub_order", function ($se) {
            return $se->hub_order;
        });
        $contentBuilder->addAbstract("hub_type", function ($se) {
            $doc = SEManager::getDocument($se->initid)->fromid;
            $fam = SEManager::getFamily($doc);
            return array("value" => $fam->getTitle(), "displayValue" => $fam->getTitle());
        });
        return $contentBuilder->getContent();
    }

    protected static function getFilterable($type)
    {
        $operators = Operators::getTypeOperators($type);

        if (!$operators) {
            return false;
        }

        $stringsOperators = [];
        foreach ($operators as $k => $operator) {
            if (!empty($operator["typedLabels"])) {
                $stringsOperators[$k] = $operator["typedLabels"][$type] ?? $operator["label"];
            } else {
                $stringsOperators[$k] = $operator["label"];
            }
        }


        $filterable = [
            "operators" => [
                "string" => $stringsOperators,
                "date" => $stringsOperators,
            ],
            "cell" => [
                "enable" => true,
                "delay" => 9999999999 // Wait 115 days : only way to have the clear button easyly
            ]
        ];
        return $filterable;
    }
}
