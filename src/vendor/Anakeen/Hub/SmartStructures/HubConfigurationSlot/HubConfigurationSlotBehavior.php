<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationSlot;

class HubConfigurationSlotBehavior extends \SmartStructure\Hubconfiguration
{
    public function registerHooks()
    {
        parent::registerHooks();
    }

    public function getConfiguration()
    {
        $configuration = parent::getConfiguration();
        return $configuration;
    }

    protected function getComponentConfiguration()
    {
        return parent::getComponentConfiguration();
    }
}
