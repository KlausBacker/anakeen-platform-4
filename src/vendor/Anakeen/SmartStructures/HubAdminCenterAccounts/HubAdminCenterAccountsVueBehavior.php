<?php

namespace Anakeen\SmartStructures\HubAdminCenterAccounts;

class HubAdminCenterAccountsVueBehavior extends Anakeen\Hub\SmartStructures\HubConfiguration
{

    public function registerHooks()
    {
        parent::registerHooks();
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
            "componentName" => "ank-admin-account",

            // Properties to use for the components
            "props" => []
        ];
    }
}
