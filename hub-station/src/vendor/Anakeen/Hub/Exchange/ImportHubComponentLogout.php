<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfiguration as ComponentFields;
use SmartStructure\Fields\Hubconfigurationlogout as LogoutFields;

class ImportHubComponentLogout extends ImportHubComponent
{
    protected function getCustomMapping()
    {
        $customMapping=parent::getCustomMapping();
        $this->smartNs = HubExportLogoutComponent::$nsUrl;
        $this->defaultNsPrefix = "hubcl";

        $mapping = [
            "title" => LogoutFields::logout_title,
        ];



        return array_merge($customMapping, $mapping);
    }

    protected function getXPath($prefix)
    {
        $xpath = parent::getXPath($prefix);
        $xpath->registerNamespace(
            "hubcl",
            HubExportLogoutComponent::$nsUrl
        );

        return $xpath;
    }
}
