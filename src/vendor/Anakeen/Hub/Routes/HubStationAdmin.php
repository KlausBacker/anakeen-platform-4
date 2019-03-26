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
    protected $slice = 1000;

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
     * @throws \Anakeen\Search\Internal\SearchSmartData\Exception
     */
    protected function prepareFiltering()
    {
        parent::prepareFiltering();
        $this->structure = SEManager::getDocument($this->structureName);
        // search in all parent structure
        $this->_searchDoc->setOrder(Fields::hub_docker_position.','.Fields::hub_order);
        $this->_searchDoc->addFilter("%s = '%s'", Fields::hub_station_id, $this->structure->initid);
    }

    /**
     * @return array
     * @throws \Anakeen\Core\DocManager\Exception
     */
    protected function getData()
    {
        $parent = parent::getData();
        foreach ($parent["smartElements"] as $key => $datum) {
            $doc = SEManager::getDocument($parent["smartElements"][$key]["properties"]["initid"])->fromid;
            $fam = SEManager::getFamily($doc);
            $parent["smartElements"][$key]["attributes"]["hub_type"] = array("value" => $fam->getTitle(), "displayValue" => $fam->getTitle());
        }
        return $parent;
    }
}
