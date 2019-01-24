<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationSlot;

use SmartStructure\Fields\Hubconfigurationslot as HubConfigurationSlotFields;

class HubConfigurationSlotBehavior extends \SmartStructure\Hubconfiguration
{
    public function registerHooks()
    {
        parent::registerHooks();
    }

    public function getConfiguration()
    {
        $configuration = parent::getConfiguration();

        $configuration["dock"] = $this->getAttributeValue(HubConfigurationSlotFields::hub_docker_position);
        $configuration["tab"] = [];
        $configuration["tab"]["expanded"] = "<span>".$this->getHubConfigurationTitle()."</span>";

        // Slot element of the Hub are not selectable, and not selected
        $configuration["tab"]["selectable"] = false;
        $configuration["tab"]["selected"] = false;

        // No content is provided in slot element
        $configuration["tab"]["content"] = "";
        $configuration["tab"]["compact"] = $this->getComponentConfiguration();

        return $configuration;
    }

    protected function getComponentConfiguration()
    {
        return parent::getComponentConfiguration();
    }

}
