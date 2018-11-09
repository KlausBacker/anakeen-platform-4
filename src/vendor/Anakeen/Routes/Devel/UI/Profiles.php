<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\Routes\GridContent;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\Format\UnknowAttributeValue;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use SearchDoc;

/**
 * Get Profiles
 *
 * @note Used by route : GET api/v2/devel/security/profiles/
 */
class Profiles extends GridContent
{

    protected $postFilterItems = [];

    protected function parseUrlArgs($urlArgs = array())
    {
        $this->smartElementId = 0;
    }

    protected function prepareSearchDoc()
    {
        $this->_searchDoc = new SearchDoc();
        $this->_searchDoc->setObjectReturn();
        $this->_searchDoc->excludeConfidential(true);
    }

    protected function prepareFiltering()
    {
        $this->_searchDoc->addFilter("profid = id and (dprofid is null or dprofid = 0)");
        if (!empty($this->filter)) {
            // First need flat filters
            $flatFilters = static::getFlatLevelFilters($this->filter);

            foreach ($flatFilters as $filter) {
                if (isset($filter["field"]) && $filter["field"] === "dpdoc_famid") {
                    $this->postFilterItems[] = $filter;
                } elseif (isset($filter["field"]) && $filter["field"] === "fromid") {
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

    protected function prepareDocumentFormatter($documentList)
    {
        $formatter = parent::prepareDocumentFormatter($documentList);
        $formatter->getFormatCollection()->setAttributeRenderHook(function ($attrValue, $attrId) {
            // Return empty value for non existing attribute in elements
            if (empty($attrValue)) {
                return new UnknowAttributeValue('');
            }
            return $attrValue;
        });
        $formatter->getFormatCollection()->setDocumentRenderHook(function ($info, SmartElement $doc) {
            $famid = $doc->getRawValue("dpdoc_famid");
            if ($famid) {
                $info["attributes"]["dpdoc_famid"]->name = SEManager::getNameFromId($famid);
            }
            return $info;
        });
        return $formatter;
    }

    protected function getData()
    {
        $data = parent::getData();
        $elements = $data["smartElements"];
        if (!empty($this->postFilterItems)) {
            foreach ($this->postFilterItems as $filter) {
                $elements = array_filter($elements, function ($item) use ($filter) {
                    if (isset($item["attributes"][$filter["field"]])) {
                        return $item["attributes"][$filter["field"]]->value === $filter["value"];
                    }
                    return false;
                });
            }
        }
        $data["smartElements"] = array_values($elements);
        return $data;
    }
}
