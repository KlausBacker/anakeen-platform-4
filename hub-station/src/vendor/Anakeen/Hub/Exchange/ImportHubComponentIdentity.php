<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfigurationidentity as IdentityFields;

class ImportHubComponentIdentity extends ImportHubComponent
{
    public function getCustomMapping()
    {

        $customMapping=parent::getCustomMapping();
        $this->smartNs = HubExportIdentityComponent::$nsUrl;
        $this->defaultNsPrefix = "hubci";

        $mapping = [
            "alterable-email" => IdentityFields::email_alterable,
            "alterable-password" => IdentityFields::password_alterable,
        ];

        return array_merge($customMapping, $mapping);
    }

    protected function getXPath($prefix)
    {
        $xpath = parent::getXPath($prefix);
        $xpath->registerNamespace(
            "hubci",
            HubExportIdentityComponent::$nsUrl
        );

        return $xpath;
    }
}
