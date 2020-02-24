<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;
use Anakeen\Core\SEManager;

class ProfilesGridController extends DefaultGridController
{
    public static function getGridConfig($collectionId, $clientConfig)
    {
        $configBuilder = new SmartGridConfigBuilder();
        if (isset($clientConfig["pageable"])) {
            $configBuilder->setPageable($clientConfig["pageable"]);
        }
        $configBuilder->addProperty("title");
        $configBuilder->addProperty("name");
        $configBuilder->addProperty("fromid", array("relation"=>-1, "smartType"=>"text","title"=>"Type"));
        $configBuilder->addField("dpdoc_famid", array("title"=>"Dynamic", "smartType"=>"text", "abstract"=>true), "PDOC");
        $configBuilder->addRowAction(array("action"=>"view", "title"=>"View Rights"));
        return $configBuilder->getConfig();
    }

    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();
        $contentBuilder->setCollection("PDOC");
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
        $contentBuilder->addProperty("name");
        $contentBuilder->addProperty("title");
        $contentBuilder->addProperty("fromid");
        $contentBuilder->addAbstract("dpdoc_famid", function ($se) {
            $name = SEManager::getNameFromId($se->dpdoc_famid);
            return $name;
        });
        $fullContent = $contentBuilder->getContent();
        $content = $fullContent["content"];
        foreach ($content as $key => $val) {
            $fullContent["content"][$key]["properties"]["fromid"] = SEManager::getNameFromId($val["properties"]["fromid"]);
        }
        return $fullContent;
    }
}
