<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfigurationgeneric as GenericFields;

class HubExportGenericComponent extends HubExportVueComponent
{

    protected $mainTag="component-generic";


    protected function getParameters()
    {

        $parameters = parent::getParameters();



        $this->addFieldArrayTwoColumns(
            GenericFields::hge_cssasset,
            "css",
            GenericFields::hge_cssasset_type,
            "type",
            $parameters
        );
        $this->addFieldArrayTwoColumns(
            GenericFields::hge_jsasset,
            "js",
            GenericFields::hge_jsasset_type,
            "type",
            $parameters
        );

        $this->addField(GenericFields::hge_component_tag, "component-tag", $parameters);
        $this->addField(GenericFields::hge_component_props, "component-props", $parameters);
        return $parameters;
    }
}
