<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfigurationidentity as ComponentIdentityFields;

class HubExportIdentityComponent extends HubExportComponent
{
    protected $mainTag="component-identity";
    public function appendTo(\DOMElement $parent)
    {
        $node=parent::appendTo($parent);

        $node->appendChild($this->getParameters());
        return $node;
    }


    protected function getParameters()
    {

        $parameters = $this->cel("parameters");


        $this->addField(ComponentIdentityFields::email_alterable, "alterable-email", $parameters);
        $this->addField(ComponentIdentityFields::password_alterable, "alterable-password", $parameters);


        return $parameters;
    }
}
