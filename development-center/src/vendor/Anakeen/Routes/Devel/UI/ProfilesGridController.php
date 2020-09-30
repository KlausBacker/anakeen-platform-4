<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Search\Filters\EqualsOne;

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
        $configBuilder->addProperty("fromid", array("relation" => -1, "smartType" => "text", "title" => "Type"));
        $configBuilder->addField(
            "dpdoc_famid",
            array("title" => "Dynamic", "smartType" => "text", "abstract" => true),
            "PDOC"
        );
        $configBuilder->addRowAction(array("action" => "view", "title" => "View Rights"));
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
            foreach ($clientConfig["filter"]["filters"] as $filter) {
                switch ($filter["field"]) {
                    case "fromid":
                        if (isset($filter["filters"]) && !empty($filter["filters"])) {
                            foreach ($filter["filters"] as $fromIdFilter) {
                                if (strcmp($fromIdFilter["operator"], "titleContains") !== 0) {
                                    $contentBuilder->addFilter($fromIdFilter);
                                } else {
                                    $sqlQuery = "SELECT id FROM docfam WHERE name ~* '%s'";
                                    if (isset($fromIdFilter["value"]) && !empty($fromIdFilter["value"])) {
                                        $result = [];
                                        DbManager::query(
                                            sprintf($sqlQuery, pg_escape_string($fromIdFilter["value"])),
                                            $result,
                                            true
                                        );
                                        if (empty($result)) {
                                            // Ensure that no filter result implies there is no grid data content (fromid = -2)
                                            $result = [-2];
                                        }
                                        $contentBuilder->getSearch()->addFilter(
                                            "fromid in (%s)",
                                            implode(",", $result)
                                        );
                                    }
                                }
                            }
                        }
                        break;
                    case "dpdoc_famid":
                        if (isset($filter["filters"]) && !empty($filter["filters"])) {
                            foreach ($filter["filters"] as $f) {
                                if (strcmp($f["operator"], "titleContains") === 0) {
                                    $sqlQuery = "SELECT id FROM docfam WHERE name ~* '%s'";
                                    if (isset($f["value"]) && !empty($f["value"])) {
                                        $result = [];
                                        DbManager::query(
                                            sprintf($sqlQuery, pg_escape_string($f["value"])),
                                            $result,
                                            true
                                        );
                                        if (empty($result)) {
                                            // Ensure that no filter result implies there is no grid data content (fromid = -2)
                                            $result = [-2];
                                        }
                                        $contentBuilder->getSearch()->addFilter(new EqualsOne("dpdoc_famid", $result));
                                    }
                                }
                            }
                        } else {
                            if (strcmp($filter["operator"], "contains") === 0) {
                                $sqlQuery = "SELECT id FROM docfam WHERE name ~* '%s'";
                                if (isset($filter["value"]) && !empty($filter["value"])) {
                                    $result = [];
                                    DbManager::query(
                                        sprintf($sqlQuery, pg_escape_string($filter["value"])),
                                        $result,
                                        true
                                    );
                                    if (empty($result)) {
                                        // Ensure that no filter result implies there is no grid data content (fromid = -2)
                                        $result = [-2];
                                    }
                                    $contentBuilder->getSearch()->addFilter(new EqualsOne("dpdoc_famid", $result));
                                }
                            }
                        }
                        break;
                    default:
                        $contentBuilder->addFilter($filter);
                        break;
                }
            }
        }
        if (isset($clientConfig["sort"])) {
            foreach ($clientConfig["sort"] as $sort) {
                $contentBuilder->addSort($sort["field"], $sort["dir"]);
            }
        }
        $contentBuilder->addProperty("name");
        $contentBuilder->addProperty("title");
        $contentBuilder->addProperty("fromid");
        $contentBuilder->addAbstract("dpdoc_famid", array("dataFunction" => function ($se) {
            return SEManager::getNameFromId($se->dpdoc_famid);
        }));
        $fullContent = $contentBuilder->getContent();
        $content = $fullContent["content"];
        foreach ($content as $key => $val) {
            $fullContent["content"][$key]["properties"]["fromid"] = SEManager::getNameFromId($val["properties"]["fromid"]);
        }
        return $fullContent;
    }
}
