<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\Routes\GridContent;
use Anakeen\Core\Internal\Format\UnknowAttributeValue;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Search\Internal\SearchSmartData;

/**
 * Get Profiles
 *
 * @note Used by route : GET api/v2/devel/security/fieldAccess/
 */
class FieldAccess extends GridContent
{

    protected $postFilterItems = [];

    protected function parseUrlArgs($urlArgs = array())
    {
        $this->smartElementId = 0;
    }

    protected function prepareSearchDoc()
    {
        $this->_searchDoc = new SearchSmartData("", "FIELDACCESSLAYERLIST");
        $this->_searchDoc->setObjectReturn();
        $this->_searchDoc->excludeConfidential(true);
    }

    protected function prepareFiltering()
    {
        if (!empty($this->filter)) {
            // First need flat filters
            $flatFilters = static::getFlatLevelFilters($this->filter);

            foreach ($flatFilters as $filter) {
                if (isset($filter["field"]) && ($filter["field"] === "dpdoc_famid" || $filter["field"] === "fall_famid")) {
                    $this->postFilterItems[] = $filter;
                } else {
                    $filterObject = Operators::getFilterObject($filter);
                    if (!empty($filterObject)) {
                        $this->_searchDoc->addFilter($filterObject);
                    }
                }
            }
        }
    }

    protected function prepareDocumentFormatter($documentList)
    {
        $formatter = parent::prepareDocumentFormatter($documentList);
        $formatter->getFormatCollection()->setAttributeRenderHook(function ($attrValue) {
            // Return empty value for non existing attribute in elements
            if (empty($attrValue)) {
                return new UnknowAttributeValue('');
            }
            return $attrValue;
        });
        $formatter->getFormatCollection()->setDocumentRenderHook(function ($info, SmartElement $doc) {
            $dynfamid = $doc->getRawValue("dpdoc_famid");
            if ($dynfamid) {
                $info["attributes"]["dpdoc_famid"]->name = SEManager::getNameFromId($dynfamid);
            }
            $famid = $doc->getRawValue("fall_famid");
            if ($famid) {
                $info["attributes"]["fall_famid"]->name = SEManager::getNameFromId($famid);
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
                        if (isset($item["attributes"][$filter["field"]]->name)) {
                            return strpos($item["attributes"][$filter["field"]]->name, "".$filter["value"]) !== false;
                        }
                        return strpos($item["attributes"][$filter["field"]]->value, "".$filter["value"]) !== false;
                    }
                    return false;
                });
            }
        }
        $data["smartElements"] = array_values($elements);
        return $data;
    }
}
