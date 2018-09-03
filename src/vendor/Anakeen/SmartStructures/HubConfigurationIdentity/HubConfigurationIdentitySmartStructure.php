<?php

namespace Anakeen\SmartStructures\HubConfigurationIdentity;

use SmartStructure\Fields\Hubconfigurationidentity as HubConfigurationIdentityFields;
use \Anakeen\SmartStructures\HubConfigurationSlot\HubConfigurationSlotSmartStructure;

class HubConfigurationIdentitySmartStructure extends \SmartStructure\Hubconfigurationslot
{

    public function registerHooks()
    {
        parent::registerHooks();
    }

    /**
     * Get component specific configuration, to display it correctly with its options
     * @return array
     * @throws \Dcp\Exception
     */
    protected function getComponentConfiguration()
    {
        return [
            // Name of the Vue.js component
            "componentName" => "ank-identity",

            // Properties to use for the component
            "props" => [
                "emailAlterable" => ($this->getAttributeValue(HubConfigurationIdentityFields::email_alterable) == "TRUE"),
                "passwordAlterable" => ($this->getAttributeValue(HubConfigurationIdentityFields::password_alterable) == "TRUE")
            ]
        ];
    }
}
