<?php


namespace Anakeen\Hub\Exchange;

use Anakeen\Core\SmartStructure\ExportConfiguration;
use SmartStructure\Fields\Hubconfigurationgeneric as GenericFields;

class HubExportGenericComponent extends HubExportVueComponent
{

    public static $nsUrl= ExportConfiguration::NSBASEURL . "hub-component-generic/1.0";
    protected $nsPrefix = "hubc-generic";
    protected $mainTag="config";


    protected function getParameters()
    {

        $parameters = parent::getParameters();

       // $this->dom->documentElement->setAttribute("xmlns:" . static::NSHUB, static::NSHUBURL);


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
