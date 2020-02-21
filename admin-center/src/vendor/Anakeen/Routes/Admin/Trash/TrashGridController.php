<?php


namespace Anakeen\Routes\Admin\Trash;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;
use Anakeen\Core\SEManager;
use Anakeen\Core\Utils\Date;
use Anakeen\Search\SearchElements;

class TrashGridController extends DefaultGridController
{
    public static function getGridConfig($collectionId, $clientConfig)
    {
        $configBuilder = new SmartGridConfigBuilder();
        if (isset($clientConfig["pageable"])) {
            $configBuilder->setPageable($clientConfig["pageable"]);
        }
        $configBuilder->addProperty("title");
        $configBuilder->addProperty("fromid", array("relation"=>"-1","smartType"=>"text","title"=>"Type"));
        $configBuilder->addProperty("mdate", array("title"=>"Date of deletion"));
        $configBuilder->addAbstractColumn("auth", array("smartType"=>"text","title"=>"Authorization","hidden"=>true,"sortable"=>false,"filterable"=>false));
        $configBuilder->addAbstractColumn("author", array("smartType"=>"text","title"=>"Author of the deletion","sortable"=>false,"filterable"=>true));
        $configBuilder->addRowAction(array("action"=> "restore", "title"=> "Restore"));
        $configBuilder->addRowAction(array("action"=> "display", "title"=> "Display"));
        $configBuilder->addRowAction(array("action"=> "delete", "title"=> "Delete from trash"));
        $config = $configBuilder->getConfig();
        return $config;
    }

    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();
        // Searching in all Smart Structures
        $contentBuilder->setCollection(-1);
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
            foreach ($clientConfig["filter"]["filters"] as $filter) {
                if (strcmp($filter["field"], "author") === 0) {
                    $filters = $filter["filters"];
                    foreach ($filters as $f) {
                        if ($f["operator"] === "contains") {
                            $contentBuilder->getSearch()->addFilter("dochisto.uname ILIKE '%%%s%%'", $f["value"]);
                        } elseif ($f["operator"] === "startswith") {
                            $contentBuilder->getSearch()->addFilter("dochisto.uname ILIKE '%s%%'", $f["value"]);
                        } elseif ($f["operator"] === "doesnotcontain") {
                            $contentBuilder->getSearch()->addFilter(
                                "dochisto.uname NOT ILIKE '%%%s%%'",
                                $f["value"]
                            );
                        } elseif ($f["operator"] === "isempty") {
                            $contentBuilder->getSearch()->addFilter("dochisto.uname IS NULL OR dochisto.uname = ''");
                        } elseif ($f["operator"] === "isnotempty") {
                            $contentBuilder->getSearch()->addFilter("dochisto.uname IS NOT NULL AND dochisto.uname != ''");
                        }
                    }
                } else {
                    $contentBuilder->   addFilter($filter);
                }
            }
        }
        if (isset($clientConfig["sort"])) {
            foreach ($clientConfig["sort"] as $sort) {
                $contentBuilder->addSort($sort["field"], $sort["dir"]);
            }
        }
        $contentBuilder->addProperty("fromid");
        $contentBuilder->addProperty("mdate");
        $trashSearchElements = new TrashSearchElements();
        $trashContent = new TrashContent();
        $contentBuilder->setSearch($trashSearchElements);
        $contentBuilder->getSearch()->useTrash(SearchElements::ONLYTRASH);
        $contentBuilder->getSearch()->setDistinct(true);
        $contentBuilder->getSearch()->overrideAccessControl();
        $contentBuilder->getSearch()->setOrder("dochisto.date desc");
        $contentBuilder->getSearch()->join("id = dochisto(id)");
        $contentBuilder->getSearch()->addFilter("dochisto.code = 'DELETE'");
        $contentBuilder->addAbstract("auth", function ($seData) use ($trashContent) {
            return $trashContent->canDisplay($seData);
        });
        $contentBuilder->addAbstract("author", function ($seData) use ($trashContent) {
            return $trashContent->getAuthorName($seData);
        });
        $fullContent = $contentBuilder->getContent();
        $content = $fullContent["content"];
        foreach ($content as $key => $val) {
            $fullContent["content"][$key]["properties"]["fromid"] = SEManager::getTitle($val["properties"]["fromid"]);
            $fullContent["content"][$key]["properties"]["mdate"] = Date::stringDateToLocaleDate($val["properties"]["mdate"]);
        }
        return $fullContent;
    }
}
