<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfiguration as ComponentFields;
use SmartStructure\Fields\Hubconfigurationgeneric as GenericFields;

class ImportHubComponentGeneric extends ImportHubComponentVue
{
    protected function getCustomMapping()
    {
        $this->smartNs = HubExportGenericComponent::$nsUrl;
        $this->defaultNsPrefix = "hubcg";
        $customMapping=parent::getCustomMapping();

        $mapping = [
            "css/@type" => [
                GenericFields::hge_cssasset_type,
                function ($v) {
                    return strtoupper($v);
                }
            ],
            "css" => [
                GenericFields::hge_cssasset,
                function ($v, $nodeCss) {

                    return $this->getAssetConfig($nodeCss);
                }
            ],
            "component-tag" => GenericFields::hge_component_tag,
            "js/@type" => [
                GenericFields::hge_jsasset_type,
                function ($v) {
                    return strtoupper($v);
                }
            ],
            "component-props" => GenericFields::hge_component_props,
            "js" => [
                GenericFields::hge_jsasset,
                function ($v, $nodeJs) {
                    return $this->getAssetConfig($nodeJs);
                }
            ],


        ];

        return array_merge($customMapping, $mapping);
    }

    protected function getXPath($prefix)
    {
        $xpath = parent::getXPath($prefix);
        $xpath->registerNamespace(
            "hubcg",
            HubExportGenericComponent::$nsUrl
        );

        return $xpath;
    }
}
