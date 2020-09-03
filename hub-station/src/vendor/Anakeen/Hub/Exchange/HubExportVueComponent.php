<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfigurationvue as ComponentVueFields;

class HubExportVueComponent extends HubExportComponent
{
    protected $mainTag="component-vue";
    public function appendTo(\DOMElement $parent)
    {
        $node=parent::appendTo($parent);

        $node->appendChild($this->getParameters());
        return $node;
    }


    protected function getParameters()
    {

        $parameters = $this->cel("parameters");


        $this->addField(ComponentVueFields::hub_vue_router_entry, "vue-router-entry", $parameters);


        return $parameters;
    }
}
