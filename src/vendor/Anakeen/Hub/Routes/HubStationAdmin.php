<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Components\Grid\Routes\GridContent;
use Anakeen\Core\SEManager;
use Anakeen\Search\Internal\SearchSmartData;
use SmartStructure\Fields\Hubconfiguration as Fields;

class HubStationAdmin extends GridContent
{
    protected $structureName = "";
    protected $structure = null;

    /**
     * @param array $urlArgs
     */
    protected function parseUrlArgs($urlArgs = array())
    {
        $this->smartElementId = 0;
        $this->structureName = $urlArgs["hubId"];
    }

    protected function prepareSearchDoc()
    {
        $this->_searchDoc = new SearchSmartData("", "HUBCONFIGURATION");
        $this->_searchDoc->setObjectReturn();
        $this->_searchDoc->excludeConfidential(true);
    }

    /**
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Dcp\SearchDoc\Exception
     */
    protected function prepareFiltering()
    {
        parent::prepareFiltering();
        $this->structure = SEManager::getDocument($this->structureName);
        // search in all parent structure

        $this->_searchDoc->addFilter("%s = '%s'", Fields::hub_station_id, $this->structure->initid);
    }
}
