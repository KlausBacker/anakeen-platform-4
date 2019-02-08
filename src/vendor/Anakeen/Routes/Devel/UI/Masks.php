<?php


namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\Routes\GridContent;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use SmartStructure\Fields\Mask as MskFields;

class Masks extends GridContent
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
        $this->_searchDoc = new \Anakeen\Search\Internal\SearchSmartData("", "MASK");
        $this->_searchDoc->setObjectReturn();
        $this->_searchDoc->excludeConfidential(true);
    }

    protected function prepareFiltering()
    {
        parent::prepareFiltering();
        $this->structure = SEManager::getFamily($this->structureName);
        // search in all parent structure
        $this->_searchDoc->addFilter(DbManager::getSqlOrCond($this->structure->attributes->fromids, MskFields::msk_famid));
    }
}
