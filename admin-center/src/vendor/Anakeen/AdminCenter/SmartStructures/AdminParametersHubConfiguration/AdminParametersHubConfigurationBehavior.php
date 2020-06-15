<?php

namespace Anakeen\AdminCenter\SmartStructures\AdminParametersHubConfiguration;

use Anakeen\Core\SmartStructure\Callables\InputArgument;
use Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod;
use Anakeen\Exception;
use Anakeen\SmartHooks;
use phpDocumentor\Reflection\Types\Boolean;
use SmartStructure\Fields\Adminparametershubconfiguration as AdminParametersHubConfigFields;

class AdminParametersHubConfigurationBehavior extends \SmartStructure\Hubconfigurationgeneric
{
    protected function getComponentConfiguration()
    {
        $config = parent::getComponentConfiguration();
        $config["props"] = [
            "hasGlobal" => $this->getAttributeValue(AdminParametersHubConfigFields::admin_hub_configuration_global) === "TRUE",
            "hasUsers" => $this->getAttributeValue(AdminParametersHubConfigFields::admin_hub_configuration_user) === "TRUE",
            "specificUser" => $this->getAttributeValue(AdminParametersHubConfigFields::admin_hub_configuration_account),
            "namespace" => $this->getAttributeValue(AdminParametersHubConfigFields::admin_hub_configuration_namespace)
        ];
        return $config;
    }

    public static function getParameterAssetPath()
    {
        // Return callable string for retrieve the good asset
        return "Anakeen\Hub\SmartStructures\HubConfigurationGeneric\HubAssetPath::getJSPath('admin', 'AdminParameterManager')";
    }

    public static function checkGlobalTab(string $hasGlobal, string $specificUser)
    {
        if ($specificUser) {
            if ($hasGlobal === "TRUE") {
                return ___(
                    "Global button need to be false with specific user option",
                    "AdminCenterParameters.global btn specific user"
                );
            }
        }
        return "";
    }

    public static function checkUserTab(string $hasUser, string $specificUser)
    {
        if ($specificUser) {
            if ($hasUser === "FALSE") {
                return ___(
                    "You need to turn on the button user parameters",
                    "AdminCenterParameters.user btn specific user"
                );
            }
        }
        return "";
    }
}
