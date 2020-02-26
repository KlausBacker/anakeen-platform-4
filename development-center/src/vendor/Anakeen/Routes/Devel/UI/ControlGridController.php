<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use SmartStructure\Fields\Cvdoc as CvFields;

class ControlGridController extends DefaultGridController
{
    public static function getGridConfig($collectionId, $clientConfig)
    {
        $configBuilder = new SmartGridConfigBuilder();
        if (isset($clientConfig["pageable"])) {
            $configBuilder->setPageable($clientConfig["pageable"]);
        }
        $configBuilder->addProperty("title");
        $configBuilder->addProperty("name");
//        $configBuilder->addField("cv_renderaccessclass", array("hidden"=>true));
//        $configBuilder->addField("cv_primarymask", array("hidden"=>true));
        $configBuilder->addRowAction(array("action"=>"consult", "title"=>"Consult"));
        $configBuilder->addRowAction(array("action"=>"permissions", "title"=>"Permissions"));
        return $configBuilder->getConfig();
    }

    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();
        $contentBuilder->setCollection("CVDOC");
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

        $structure = SEManager::getFamily($collectionId);
        // search in all parent structure
        $contentBuilder->getSearch()->addFilter(DbManager::getSqlOrCond($structure->attributes->fromids, CvFields::cv_famid));
        return $contentBuilder->getContent();
    }
}
