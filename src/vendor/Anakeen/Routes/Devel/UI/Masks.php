<?php


namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\Routes\GridContent;
use Anakeen\Core\SEManager;

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
        $this->_searchDoc = new \SearchDoc("", "MASK");
        $this->_searchDoc->setObjectReturn();
        $this->_searchDoc->excludeConfidential(true);
    }

    protected function prepareFiltering()
    {
        parent::prepareFiltering();
        $this->structure = SEManager::getFamily($this->structureName);
        $this->_searchDoc->addFilter("msk_famid = '".$this->structure->initid."'");
    }
}
