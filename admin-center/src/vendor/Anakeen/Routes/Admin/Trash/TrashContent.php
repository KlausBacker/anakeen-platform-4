<?php

namespace Anakeen\Routes\Admin\Trash;

use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\Routes\GridContent;
use Anakeen\Core\DbManager;
use Anakeen\Search\Internal\SearchSmartData;

/**
 * Get Trash content
 *
 * @note Used by route : GET /api/v2/admin/trash/content/
 */
class TrashContent extends GridContent
{

    protected function parseUrlArgs($urlArgs = array())
    {
        // For search in all structure

        $this->smartElementId = 0;
    }

    protected function prepareFiltering()
    {
        if (!empty($this->filter)) {
            // First need flat filters
            $flatFilters = static::getFlatLevelFilters($this->filter);

            foreach ($flatFilters as $filter) {
                if (isset($filter["field"]) && $filter["field"] === "fromid") {
                    $this->filterFromId($filter);
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
        // $this->_searchDoc->returnsOnly(["icon", "uname"]);
        $this->_searchDoc->excludeConfidential(true);
        // $this->_searchDoc->join("id = dochisto(id)");


    }
}
