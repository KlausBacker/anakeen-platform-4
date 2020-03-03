<?php


namespace Anakeen\Routes\Admin\Trash;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;
use Anakeen\Core\DbManager;
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
        $configBuilder->addProperty("fromid", array("relation"=>"-1","smartType"=>"docid","title"=>"Type"));
        $configBuilder->addProperty("mdate", array("title"=>"Date of deletion"));
        $configBuilder->addAbstract("auth", array("smartType"=>"text","title"=>"Authorization","hidden"=>true,"sortable"=>false,"filterable"=>false));
        $configBuilder->addAbstract("author", array("smartType"=>"text","title"=>"Author of the deletion","sortable"=>false,"filterable"=>true));
        $configBuilder->addRowAction(array("action"=> "restore", "title"=> "Restore"));
        $configBuilder->addRowAction(array("action"=> "display", "title"=> "Display"));
        $configBuilder->addRowAction(array("action"=> "delete", "title"=> "Delete from trash"));
        return $configBuilder->getConfig();
    }

    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();

        $trashContent = new TrashContent();
        $contentBuilder->addProperty("fromid");
        $contentBuilder->addProperty("mdate");
        $contentBuilder->getSearch()->useTrash(SearchElements::ONLYTRASH);
        $contentBuilder->getSearch()->setDistinct(true);
        $contentBuilder->getSearch()->overrideAccessControl();
        $contentBuilder->getSearch()->join("id = dochisto(id)");
        $contentBuilder->getSearch()->addFilter("dochisto.code = 'DELETE'");

        if (isset($clientConfig["pageable"]["pageSize"])) {
            $contentBuilder->setPageSize($clientConfig["pageable"]["pageSize"]);
        }
        if (isset($clientConfig["page"])) {
            $contentBuilder->setPage($clientConfig["page"]);
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
                } elseif (strcmp($filter["field"], "fromid") === 0) {
                    if (isset($filter["filters"]) && !empty($filter["filters"])) {
                        foreach ($filter["filters"] as $fromIdFilter) {
                            if (strcmp($fromIdFilter["operator"], "titleContains") !== 0) {
                                $contentBuilder->addFilter($fromIdFilter);
                            } else {
                                $sqlQuery = "SELECT id FROM docfam WHERE name ~* '%s'";
                                if (isset($fromIdFilter["value"]) && !empty($fromIdFilter["value"])) {
                                    $result = [];
                                    DbManager::query(sprintf($sqlQuery, pg_escape_string($fromIdFilter["value"])), $result, true);
                                    if (empty($result)) {
                                        // Ensure that no filter result implies there is no grid data content (fromid = -2)
                                        $result = [-2];
                                    }
                                    $contentBuilder->getSearch()->addFilter("fromid in (%s)", implode(",", $result));
                                }
                            }
                        }
                    }
                } else {
                    $contentBuilder->addFilter($filter);
                }
            }
        }

        $contentBuilder->addAbstract("auth", array("dataFunction" => function ($seData) use ($trashContent) {
            return $trashContent->canDisplay($seData);
        }));
        $contentBuilder->addAbstract("author", array("dataFunction" => function ($seData) use ($trashContent) {
            return $trashContent->getAuthorName($seData);
        }));
        $fullContent = $contentBuilder->getContent();
        $content = $fullContent["content"];
        foreach ($content as $key => $val) {
            $fullContent["content"][$key]["properties"]["fromid"] = SEManager::getNameFromId($val["properties"]["fromid"]);
            $fullContent["content"][$key]["properties"]["mdate"] = Date::stringDateToLocaleDate($val["properties"]["mdate"]);
        }
        return $fullContent;
    }
}
