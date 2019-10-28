<?php

namespace Anakeen\Routes\Admin\Trash;

use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\Routes\GridContent;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Search\Internal\SearchSmartData;

/**
 * Get Trash content
 *
 * @note Used by route : GET /api/v2/admin/trash/content/
 */
class TrashContent extends GridContent
{
    private $allDataColumn = [];

    protected function parseUrlArgs($urlArgs = array())
    {
        // For search in all structure

        $this->smartElementId = 0;
    }

    private function filterAuthor($filter)
    {
        if ($filter["operator"] === "contains") {
            $this->_searchDoc->addFilter("dochisto.uname ILIKE '%%%s%%'", $filter["value"]);
        } elseif ($filter["operator"] === "startswith") {
            $this->_searchDoc->addFilter("dochisto.uname ILIKE '%s%%'", $filter["value"]);
        } elseif ($filter["operator"] === "doesnotcontain") {
            $this->_searchDoc->addFilter("dochisto.uname NOT ILIKE '%%%s%%'", $filter["value"]);
        } elseif ($filter["operator"] === "isempty") {
            $this->_searchDoc->addFilter("dochisto.uname IS NULL OR dochisto.uname = ''");
        } elseif ($filter["operator"] === "isnotempty") {
            $this->_searchDoc->addFilter("dochisto.uname IS NOT NULL AND dochisto.uname != ''");
        }
    }

    protected function prepareFiltering()
    {
        if (!empty($this->filter)) {
            // First need flat filters
            $flatFilters = static::getFlatLevelFilters($this->filter);

            foreach ($flatFilters as $filter) {
                if (isset($filter["field"]) && $filter["field"] === "fromid") {
                    $this->filterFromId($filter);
                } elseif (isset($filter["field"]) && $filter["field"] === "author") {
                    $this->filterAuthor($filter);
                } else {
                    $filterObject = Operators::getFilterObject($filter);
                    if (!empty($filterObject)) {
                        $this->_searchDoc->addFilter($filterObject);
                    }
                }
            }
        }
    }

    protected function filterFromId($filter)
    {
        $sqlQuery = "SELECT id FROM docfam WHERE name ~* '%s'";
        if (isset($filter["value"]) && !empty($filter["value"])) {
            $result = [];
            DbManager::query(sprintf($sqlQuery, pg_escape_string($filter["value"])), $result, true);
            if (empty($result)) {
                $result = [-2];
            }
            $this->_searchDoc->addFilter("fromid in (%s)", implode(",", $result));
        }
    }


    protected function prepareSearchDoc()
    {
        $this->_searchDoc = new SearchSmartData();

        $this->_searchDoc->trash = "only";
        $this->_searchDoc->setObjectReturn();
        $this->_searchDoc->distinct = true;
        $this->_searchDoc->excludeConfidential(true);
        $this->_searchDoc->overrideViewControl(true);
        $this->_searchDoc->join("id = dochisto(id)");
        $this->_searchDoc->addFilter("dochisto.code = 'DELETE'");
    }

    protected function prepareDocumentList()
    {
        parent::prepareDocumentList();
        $this->_searchDoc->setOrder("dochisto.date desc");
    }




    protected static function canDisplay($seData)
    {

        $se = SEManager::getDocument($seData["properties"]["id"]);
        if (!empty($se)) {
            $err = $se->control("view");
            return empty($err);
        }
        return false;
    }

    protected function extractOrderBy()
    {
        $orderBy = parent::extractOrderBy();
        return $orderBy;
    }

    protected static function getAuthorName($seData)
    {
        $seId = $seData["properties"]["id"];
        $sql = <<<'SQL'
select distinct on(docread.initid) docread.*, dochisto.uname as deluser, dochisto.date as deldate  
from docread, dochisto 
where docread.id=%d and docread.id= dochisto.id and docread.doctype='Z' and dochisto.code = 'DELETE'
SQL;
        $result = [];
        DbManager::query(sprintf($sql, $seId), $result, false, true);
        return [
            "value" => $result["deluser"],
            "displayValue" => $result["deluser"]
        ];
    }

    protected function getData()
    {
        $parentData = parent::getData();

        foreach ($parentData["smartElements"] as $key => $smartElement) {
            $parentData["smartElements"][$key]["attributes"]["author"] = self::getAuthorName($parentData["smartElements"][$key]);
            $parentData["smartElements"][$key]["attributes"]["auth"] = [
                "value" => self::canDisplay($parentData["smartElements"][$key])
            ];
        }
        return $parentData;
    }
}
