<?php


namespace Anakeen\Hub\Exchange;

use Anakeen\Core\SmartStructure\ExportConfiguration;
use SmartStructure\Fields\Hubconfiguration as ComponentFields;

class HubExportComponent extends HubExport
{
    public static $NSHUBURLCOMPONENT = ExportConfiguration::NSBASEURL . "hub-component/1.0";
    protected $NSHUBCOMPONENT = "hub-component";
    protected $mainTag = "config";


    public function getXml()
    {
        $config = $this->setXmlConfig();

        $parameters = $this->getParameters();
        if ($parameters) {
            $config->appendChild($parameters);
        }

        $dom = new \DOMDocument("1.0", "UTF-8");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        // Need to releal to have goot indentation format
        $dom->loadXML($this->dom->saveXML());
        return $dom->saveXML();
    }

    public function setXmlConfig()
    {

        $this->domConfig = $this->cel("component");
        $originalUrl = static::$nsUrl;
        $originalPrefix = $this->nsPrefix;
        static::$nsUrl = self::$NSHUBURLCOMPONENT;
        $this->nsPrefix = $this->NSHUBCOMPONENT;
        $nodeComponent = $this->cel("parameters");


        $this->dom->documentElement->appendChild($this->domConfig);

        $this->domConfig->setAttribute(
            "instance-ref",
            $this->getLogicalName($this->smartElement->getRawValue(ComponentFields::hub_station_id))
        );
        $this->domConfig->setAttribute("name", $this->smartElement->name);

        $display = $this->cel("display", null, $nodeComponent);
        $position = explode("_", $this->smartElement->getRawValue(ComponentFields::hub_docker_position));
        $display->setAttribute("position", strtolower($position[0]));
        $display->setAttribute("placement", strtolower($position[1]));
        $hubOrder = $this->smartElement->getRawValue(ComponentFields::hub_order);
        if ($hubOrder !== "") {
            $display->setAttribute("order", $hubOrder);
        }


        $this->addField(ComponentFields::hub_title, "title", $nodeComponent);


        $nodeComponent->appendChild($this->getSettings());
        $nodeComponent->appendChild($this->getSecurity());

        $this->domConfig->appendChild($nodeComponent);


        static::$nsUrl = $originalUrl;
        $this->nsPrefix = $originalPrefix;
        return $this->domConfig;
    }


    protected function getParameters()
    {
        return null;
    }

    protected function getSecurity()
    {

        $security = $this->cel("security");


        $visibilityRoles = $this->cel("visibility-roles");
        $visibilityRoles->setAttribute("logical-operator", "or");
        if ($this->addField(ComponentFields::hub_visibility_roles, "visibility-role", $visibilityRoles)) {
            $security->appendChild($visibilityRoles);
        }

        $execRoles = $this->cel("execution-roles");
        $execRoles->setAttribute("logical-operator", "and");
        $this->addField(ComponentFields::hub_execution_roles, "execution-role", $execRoles);
        $security->appendChild($execRoles);

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
