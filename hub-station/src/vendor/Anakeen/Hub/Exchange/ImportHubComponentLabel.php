<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfigurationlabel as LabelFields;

class ImportHubComponentLabel extends ImportHubComponent
{
    protected function getCustomMapping()
    {
        $customMapping = parent::getCustomMapping();
        $this->smartNs = HubExportLabelComponent::$nsUrl;
        $this->defaultNsPrefix = "hubci";

        $mapping = [
            "label" => LabelFields::label,
            "extended-label" => LabelFields::extended_label,
        ];

        return array_merge($customMapping, $mapping);
    }

    protected function getXPath($prefix)
    {
        $xpath = parent::getXPath($prefix);
        $xpath->registerNamespace(
            "hubci",
            HubExportLabelComponent::$nsUrl
        );

        return $xpath;
    }
}
