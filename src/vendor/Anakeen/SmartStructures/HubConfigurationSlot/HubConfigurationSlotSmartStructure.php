<?php

namespace Anakeen\SmartStructures\HubConfigurationSlot;

use SmartStructure\Fields\Hubconfigurationslot as HubConfigurationSlotFields;

class HubConfigurationSlotSmartStructure extends \SmartStructure\Hubconfiguration
{
    public function registerHooks()
    {
        parent::registerHooks();
    }

    public function getConfiguration()
    {
        $configuration = [];

        $configuration["tab"] = [];
        $configuration["tab"]["expanded"] = "<span>".$this->getHubConfigurationTitle()."</span>";

        // Get position from field and add corresponding area in configuration
        $position = $this->getAttributeValue(HubConfigurationSlotFields::hub_slot_position);
        if ($position == "TOP") {
            $configuration["area"] = "header";
        } else {
            $configuration["area"] = "footer";
        }

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