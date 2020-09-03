<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfigurationlabel as ComponentLabelFields;

class HubExportLabelComponent extends HubExportComponent
{
    protected $mainTag="component-label";
    public function appendTo(\DOMElement $parent)
    {
        $node=parent::appendTo($parent);

        $node->appendChild($this->getParameters());
        return $node;
    }


    protected function getParameters()
    {

        $parameters = $this->cel("parameters");


        $this->addField(ComponentLabelFields::label, "label", $parameters);
        $this->addField(ComponentLabelFields::extended_label, "extended-label", $parameters);


        return $parameters;
    }
}
