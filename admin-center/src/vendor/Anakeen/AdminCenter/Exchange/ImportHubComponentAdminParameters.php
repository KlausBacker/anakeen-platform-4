<?php


namespace Anakeen\AdminCenter\Exchange;

use Anakeen\Hub\Exchange\ImportHubComponentGeneric;
use SmartStructure\Fields\Adminparametershubconfiguration as ParametersFields;

class ImportHubComponentAdminParameters extends ImportHubComponentGeneric
{


    protected function getCustomMapping()
    {
        $customMapping = parent::getCustomMapping();
        $this->smartNs = HubExportAdminParameterComponent::$nsUrl;
        $this->defaultNsPrefix = "hubci";

        $mapping = [
            "sidebar-icon" => ParametersFields::admin_hub_configuration_icon,
            "display-global-parameters" => [
                ParametersFields::admin_hub_configuration_global,
                function ($v) {
                    return strtoupper($v);
                }
            ],
            "specific-user/@login" => ParametersFields::admin_hub_configuration_account,
            "sidebar-label" => ParametersFields::admin_hub_configuration_label,
            "parameters-namespace" => ParametersFields::admin_hub_configuration_namespace,
            "display-users-parameters" => [
                ParametersFields::admin_hub_configuration_user,
                function ($v) {
                    return strtoupper($v);
                }
            ]
        ];

        return array_merge($customMapping, $mapping);
    }


    protected function getXPath($prefix)
    {
        $xpath = parent::getXPath($prefix);
        $xpath->registerNamespace(
            "hubci",
            HubExportAdminParameterComponent::$nsUrl
        );

        return $xpath;
    }
}
