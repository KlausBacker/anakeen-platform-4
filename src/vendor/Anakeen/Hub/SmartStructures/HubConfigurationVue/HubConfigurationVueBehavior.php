<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationVue;

use SmartStructure\Fields\Hubconfigurationvue as HubConfigurationVueFields;
class HubConfigurationVueBehavior extends \SmartStructure\Hubconfiguration
{
    public function getConfiguration()
    {
        $config = parent::getConfiguration();
        $config["tab"]["content"] = "";
        $config["tab"]["module"] = $this->getJSModuleInfo();
        return $config;
    }

    protected function getRouterInfo()
    {
        return [
            "entry" => $this->getAttributeValue(HubConfigurationVueFields::hub_vue_router_entry, uniqid("hubEntry_"))
        ];
    }

    protected function getJSModuleInfo()
    {
        return [
            "router" => $this->getRouterInfo(),
            "component" => $this->getComponentConfiguration(),
            "name" => ""
        ];
    }
}
