<?php

namespace Anakeen\AdminCenter\SmartStructures\HubAdminVaultManager;

use SmartStructure\Hubconfigurationvue;

class HubAdminVaultManagerVueBehavior extends Hubconfigurationvue
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
            "name" => "ank-admin-vault-manager",

            // Properties to use for the components
            "props" => []
        ];
    }
}
