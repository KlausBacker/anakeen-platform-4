<?php

namespace Anakeen\AdminCenter\SmartStructures\HubAuthenticationTokens;

use SmartStructure\Hubconfigurationvue;

class HubAuthenticationTokensVueBehavior extends Hubconfigurationvue
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
            "name" => "ank-hub-authentication-tokens",

            // Properties to use for the components
            "props" => []
        ];
    }
}
