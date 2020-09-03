<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfigurationlogout as ComponentLogoutFields;

class HubExportLogoutComponent extends HubExportComponent
{
    protected $mainTag="component-logout";
    public function appendTo(\DOMElement $parent)
    {
        $node=parent::appendTo($parent);

        $node->appendChild($this->getParameters());
        return $node;
    }


    protected function getParameters()
    {

        $parameters = $this->cel("parameters");


        $this->addField(ComponentLogoutFields::logout_title, "title", $parameters);


        return $parameters;
    }
}
