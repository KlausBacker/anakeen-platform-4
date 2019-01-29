<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Components\Grid\Routes\GridContent;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\SmartElement;
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
        $this->_searchDoc = new \SearchDoc("", "HUBCONFIGURATION");
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

        $this->_searchDoc->addFilter("%s = '%s'", Fields::hub_station_id,$this->structureName);
    }

    /**
     * @return array
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Dcp\Exception
     */
//    protected function getData()
//    {
//        $parent = parent::getData();
//        $data = $parent["smartElements"];
//        $result = [];
//        foreach ($data as $datum) {
//            $doc = SEManager::getDocument($datum["properties"]["id"]);
//            if ($doc) {
//                $value = [
//                    "id" => $doc->id,
//                    "title" => $doc->getCustomTitle(),
//                    "icon" => $doc->getRawValue("hub_final_icon"),
//                    "order" => $doc->getRawValue("hub_order"),
//                    "position" => $doc->getRawValue("hub_docker_position")
//                ];
//                array_push($result, $value);
//            }
//        }
////        $parent["hub"] = $result;
//        return $parent;
//    }
}
