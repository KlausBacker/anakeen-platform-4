<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\Routes\ColumnsConfig;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use SmartStructure\Fields\Mask as MskFields;

class MasksGridController extends DefaultGridController
{
    public static function getGridConfig($collectionId, $clientConfig)
    {
        $configBuilder = new SmartGridConfigBuilder();
        if (isset($clientConfig["pageable"])) {
            $configBuilder->setPageable($clientConfig["pageable"]);
        }
        $configBuilder->addProperty("title");
        $configBuilder->addProperty("name");
        $configBuilder->addRowAction(array("action"=>"consult", "title"=>"Consult"));
        $config = $configBuilder->getConfig();
        return $config;
    }

    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();
        $contentBuilder->setCollection("MASK");
//        if (isset($collectionId)) {
//            $contentBuilder->setCollection($collectionId);
//        }
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
        if (isset($clientConfig["filter"])) {
            $contentBuilder->addFilter($clientConfig["filter"]);
        }
        $contentBuilder->addProperty("name");
        $contentBuilder->addProperty("title");
        $structure = SEManager::getFamily($collectionId);
        $contentBuilder->getSearch()->addFilter(DbManager::getSqlOrCond($structure->attributes->fromids, MskFields::msk_famid));
        return $contentBuilder->getContent();
    }
}
