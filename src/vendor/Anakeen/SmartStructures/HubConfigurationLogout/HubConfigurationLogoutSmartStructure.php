<?php

namespace Anakeen\SmartStructures\HubConfigurationLogout;

use SmartStructure\Fields\Hubconfigurationlogout as HubConfigurationLogoutFields;
use \Anakeen\SmartStructures\HubConfigurationSlot\HubConfigurationSlotSmartStructure;

class HubConfigurationLogoutSmartStructure extends \SmartStructure\Hubconfigurationslot
{

    public function registerHooks()
    {
        parent::registerHooks();
    }

    /**
     * Get component specific configuration, to display it correctly with its options
     * @return array
     */
    protected function getComponentConfiguration()
    {
        return [
            // Name of the Vue.js component
            "componentName" => "ank-logout",

            // Properties to use for the components
            "props" => [
                "title" => $this->getAttributeValue(HubConfigurationLogoutFields::logout_title),
            ]
        ];
    }
}
