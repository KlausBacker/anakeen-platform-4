<?php


namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\Routes\GridContent;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Search\SearchElements;
use SmartStructure\Fields\Cvdoc as CvFields;

class Control extends GridContent
{
    protected $structureName = "";
    protected $structure = null;

    protected function parseUrlArgs($urlArgs = array())
    {
        $this->smartElementId = 0;
        $this->structureName = $urlArgs["structure"];
    }

    protected function prepareSearchDoc()
    {
        $this->_searchDoc = new \Anakeen\Search\Internal\SearchSmartData("", "CVDOC");
        $this->_searchDoc->setObjectReturn();
        $this->_searchDoc->excludeConfidential(true);
    }

    protected function prepareFiltering()
    {
        parent::prepareFiltering();
        $this->structure = SEManager::getFamily($this->structureName);
        // search in all parent structure
        $this->_searchDoc->addFilter(DbManager::getSqlOrCond($this->structure->attributes->fromids, CvFields::cv_famid));
    }

    protected function getData()
    {
        $parent = parent::getData();
        $data = $parent["smartElements"];
        $result = [];
        foreach ($data as $datum) {
            $doc = SEManager::getDocument($datum["properties"]["id"]);
            if ($doc) {
                $result["name"] = $doc->name;
                $result["title"] = $doc->title;
                $result["cv_renderaccessclass"] = $doc->getAttributeValue("cv_renderaccessclass");
                $result["cv_primarymask"] = $doc->getAttributeValue("cv_primarymask");
            }
        }
        return $parent;
    }
}
