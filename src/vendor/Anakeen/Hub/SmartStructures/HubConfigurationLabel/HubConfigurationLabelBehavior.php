<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationLabel;

use SmartStructure\Fields\Hubconfigurationlabel as HubConfigurationLabelFields;

class HubConfigurationLabelBehavior extends \SmartStructure\Hubconfiguration
{

    public function registerHooks()
    {
        parent::registerHooks();
    }

    public function getConfiguration()
    {
        // Config to return
        $configuration = parent::getConfiguration();

        $configuration["tab"]["selectable"] = false;
        $configuration["tab"]["compact"] = "";

        return $configuration;
    }

    /**
     * Get component specific configuration, to display it correctly with its options
     *
     * @return array
     */
    protected function getComponentConfiguration()
    {
        return [
            // Name of the Vue.js component
            "componentName" => "ank-logout",

            // Properties to use for the components
            "props" => [
                "title" => $this->getAttributeValue(HubConfigurationLabelFields::label),
            ]
        ];
    }
}
