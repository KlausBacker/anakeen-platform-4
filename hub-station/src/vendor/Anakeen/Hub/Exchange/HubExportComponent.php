<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfiguration as ComponentFields;

class HubExportComponent extends HubExport
{
    protected $mainTag="component";
    public function appendTo(\DOMElement $parent)
    {
        $this->dom = $parent->ownerDocument;
        $nodeComponent = $this->cel($this->mainTag);

        $nodeComponent->setAttribute(
            "instance-ref",
            $this->getLogicalName($this->smartElement->getRawValue(ComponentFields::hub_station_id))
        );
        $nodeComponent->setAttribute("name", $this->smartElement->name);
        $nodeComponent->setAttribute("order", $this->smartElement->getRawValue(ComponentFields::hub_order));

        $position = explode("_", $this->smartElement->getRawValue(ComponentFields::hub_docker_position));
        $nodeComponent->setAttribute("position", strtolower($position[0]));
        $nodeComponent->setAttribute("placement", strtolower($position[1]));


        $this->addField(ComponentFields::hub_title, "title", $nodeComponent);


        $nodeComponent->appendChild($this->getSettings());
        $nodeComponent->appendChild($this->getSecurity());

        $parent->appendChild($nodeComponent);
        return $nodeComponent;
    }

    protected function getSecurity()
    {

        $security = $this->cel("security");


        $this->addField(ComponentFields::hub_visibility_roles, "visibility-role", $security);
        $this->addField(ComponentFields::hub_execution_roles, "execution-role", $security);


        return $security;
    }

    protected function getSettings()
    {

        $setting = $this->cel("settings");

        $setting->setAttribute(
            "activated",
            strtolower($this->smartElement->getRawValue(ComponentFields::hub_activated))
        );
        $aOrder = $this->smartElement->getRawValue(ComponentFields::hub_activated_order);
        if ($aOrder) {
            $setting->setAttribute("activated-order", $aOrder);
        }
        $setting->setAttribute(
            "selectable",
            strtolower($this->smartElement->getRawValue(ComponentFields::hub_selectable))
        );
        $setting->setAttribute(
            "expandable",
            strtolower($this->smartElement->getRawValue(ComponentFields::hub_expandable))
        );


        return $setting;
    }
}
