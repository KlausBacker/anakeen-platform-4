<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfigurationvue as ComponentVueFields;

class HubExportVueComponent extends HubExportComponent
{
    protected $mainTag="component-vue";



    protected function getParameters()
    {

        $parameters = $this->cel("parameters");


        $this->addField(ComponentVueFields::hub_vue_router_entry, "router-entry", $parameters);


        return $parameters;
    }
}
