<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationVue;

use SmartStructure\Fields\Hubconfigurationvue as HubConfigurationVueFields;

class HubConfigurationVueBehavior extends \SmartStructure\Hubconfiguration
{
    public function getConfiguration()
    {
        $config = parent::getConfiguration();
        $config["entryOptions"]["route"] = $this->getAttributeValue(HubConfigurationVueFields::hub_vue_router_entry, uniqid("hubEntry_"));
        return $config;
    }
}
