<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationLabel;

use SmartStructure\Fields\Hubconfigurationlabel as HubConfigurationLabelFields;

class HubConfigurationLabelBehavior extends \SmartStructure\Hubconfigurationslot
{

    public function registerHooks()
    {
        parent::registerHooks();
    }

    /**
     * @return array
     * @throws \Anakeen\Exception
     */
    protected function getComponentConfiguration()
    {
        $longLabel=$this->getAttributeValue("extended_label");
        $label=$this->getAttributeValue("label");
        if (!$longLabel) {
            $longLabel = $label;
        }
        return [
            // Name of the Vue.js component
            "name" => "hub-label",

            // Properties to use for the components
            "props" => ["label" => $label, "extendedLabel" => $longLabel]
        ];
    }
}
