<?php

namespace Anakeen\AdminCenter\SmartStructures\AdminParametersHubConfiguration;

use Anakeen\SmartElementManager;
use SmartStructure\Fields\Adminparametershubconfiguration as AdminParametersHubConfigFields;
use Anakeen\EnumItem;

class AdminParametersHubConfigurationBehavior extends \SmartStructure\Hubconfigurationgeneric
{
    protected function getComponentConfiguration()
    {
        $login = null;
        $config = parent::getComponentConfiguration();
        $docId = $this->getAttributeValue(AdminParametersHubConfigFields::admin_hub_configuration_account);
        $doc = SmartElementManager::getDocument($docId);
        if ($doc) {
            $login = $doc->getAttributeValue("us_login");
        }
        $config["props"] = [
            "hasGlobal" => $this->getAttributeValue(AdminParametersHubConfigFields::admin_hub_configuration_global) === "TRUE",
            "hasUsers" => $this->getAttributeValue(AdminParametersHubConfigFields::admin_hub_configuration_user) === "TRUE",
            "specificUser" => $login,
            "namespace" => $this->getAttributeValue(AdminParametersHubConfigFields::admin_hub_configuration_namespace),
            "icon" => $this->getAttributeValue(AdminParametersHubConfigFields::admin_hub_configuration_icon),
            "label" => $this->getAttributeValue(AdminParametersHubConfigFields::admin_hub_configuration_label)
        ];
        return $config;
    }

    public static function getParameterAssetPath()
    {
        // Return callable string for retrieve the good asset
        return "Anakeen\Hub\SmartStructures\HubConfigurationGeneric\HubAssetPath::getJSPath('admin', 'AdminParameterManager')";
    }

    public static function getAllNameSpace()
    {
        $items = [];
        \Anakeen\Core\DbManager::query("select distinct(substring(name for position('::' in name) -1)) from paramdef", $allNamespaces, true);

        sort($allNamespaces);
        foreach ($allNamespaces as $nameSpace) {
            $items[] = new EnumItem($nameSpace);
        }
        return $items;
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
