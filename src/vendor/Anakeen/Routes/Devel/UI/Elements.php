<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\Routes\GridContent;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use SearchDoc;

/**
 * Get Profiles
 *
 * @note Used by route : GET api/v2/devel/security/elements/
 */
class Elements extends GridContent
{

    protected function parseUrlArgs($urlArgs = array())
    {
        $this->smartElementId = 0;
    }

    protected function prepareFiltering()
    {
        $this->_searchDoc->addFilter("doctype <> 'C'");
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
        $this->_searchDoc = new SearchDoc();
        $this->_searchDoc->setObjectReturn();
        $this->_searchDoc->excludeConfidential(true);
    }
}
