<?php

namespace Anakeen\SmartStructures\HubConfiguration;

use Anakeen\Core\ContextManager;
use SmartStructure\Fields\HubConfiguration as HubConfigurationFields;

class HubConfigurationSmartStructure extends \Anakeen\SmartElement
{

    public function registerHooks()
    {
        parent::registerHooks();
    }

    public function getConfiguration()
    {
        // Config to return
        $configuration = [];

        // Get current user language
        ContextManager::getLanguage();

        // TODO Get title from language

        // Get icon : Font and name of the icon
        $configuration["icon_name"] = HubConfigurationFields::hub_icon_name;
        $configuration["icon_font"] = HubConfigurationFields::hub_font_name;

        // Get roles
        $configuration["roles"] = HubConfigurationFields::hub_roles;

        // Get mono element
        $configuration["mono_element"] = HubConfigurationFields::hub_mono_element;

        // Get activation
        $configuration["activated"] = HubConfigurationFields::hub_activated;

        // Get order
        $configuration["order"] = HubConfigurationFields::hub_order;

        // Get id of SSHC for router
        $configuration["id"] = HubConfigurationFields::hub_id;

        return $configuration;
    }
}
