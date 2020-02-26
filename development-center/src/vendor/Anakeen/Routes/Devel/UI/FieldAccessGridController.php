<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;
use Anakeen\Core\SEManager;

/**
 * Get Field Access grid config
 *
 * @note Used by route : GET api/v2/devel/security/fieldAccess/gridConfig
 */
class FieldAccessGridController extends DefaultGridController
{
    public static function getGridConfig($collectionId, $clientConfig)
    {
        $configBuilder = new SmartGridConfigBuilder();
        if (isset($clientConfig["pageable"])) {
            $configBuilder->setPageable($clientConfig["pageable"]);
        }
        $configBuilder->addProperty("title");
        $configBuilder->addProperty("name");
        $configBuilder->addField("fall_famid", array("smartType"=>"text","abstract"=>true), "FIELDACCESSLAYERLIST");
        $configBuilder->addField("dpdoc_famid", array("title"=>"Dynamic", "smartType"=>"text", "abstract"=>true), "FIELDACCESSLAYERLIST");
        $configBuilder->addRowAction(array("action"=>"rights", "title"=>"View Rights"));
        $configBuilder->addRowAction(array("action"=>"config", "title"=>"View Config"));
        return $configBuilder->getConfig();
    }

    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();
        $contentBuilder->setCollection("FIELDACCESSLAYERLIST");
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
        if (isset($clientConfig["sort"])) {
            foreach ($clientConfig["sort"] as $sort) {
                $contentBuilder->addSort($sort["field"], $sort["dir"]);
            }
        }
        $contentBuilder->addProperty("name");
        $contentBuilder->addProperty("title");
        $contentBuilder->addAbstract("dpdoc_famid", function ($se) {
            $name = SEManager::getNameFromId($se->dpdoc_famid);
            return $name;
        });
        $contentBuilder->addAbstract("fall_famid", function ($se) {
            $name = SEManager::getNameFromId($se->fall_famid);
            return $name;
        });
        return $contentBuilder->getContent();
    }
}
