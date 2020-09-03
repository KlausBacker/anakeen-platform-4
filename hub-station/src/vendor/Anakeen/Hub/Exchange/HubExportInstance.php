<?php


namespace Anakeen\Hub\Exchange;

use Anakeen\AdminCenter\Exchange\HubExportAdminParameterComponent;
use Anakeen\BusinessApp\Exchange\HubExportBusinessAppComponent;
use Anakeen\Core\SmartStructure\ExportConfiguration;
use Anakeen\Exception;
use Anakeen\Search\Internal\SearchSmartData;
use SmartStructure\Fields\Hubconfiguration as Fields;
use SmartStructure\Fields\Hubinstanciation as HubFields;

class HubExportInstance extends HubExport
{
    public function getXml()
    {
        $domConfig = $this->initDom();
        $domConfig->setAttribute("xmlns:" . self::NSHUB, self::NSHUBURL);
        $domConfig->appendChild($this->getInstance());

        $domConfig->appendChild($this->exportHubComponents());

        //  $domConfig->appendChild($this->getAccessProfile($name));

        // Need to reload to indent all xml structure
        $dom = new \DOMDocument();
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($this->dom->saveXML());

        return $dom->saveXML();
    }


    protected function getInstance()
    {
        $instance = $this->cel("instance");

        $instance->setAttribute("name", ExportConfiguration::getLogicalName($this->smartElement->id));
        $instance->setAttribute("title", $this->smartElement->getTitle());
        $instance->appendChild($this->getDescription());
        $instance->appendChild($this->getSetting());
        $instance->appendChild($this->getSecurity());
        return $instance;
    }


    protected function exportHubComponents()
    {
        $components = $this->cel("components");

        $search = new SearchSmartData("", "HUBCONFIGURATION");
        $search->overrideViewControl();
        $search->addFilter("%s = '%s'", Fields::hub_station_id, $this->smartElement->initid);
        $search->setOrder(Fields::hub_docker_position . ',' . Fields::hub_order);
        $search->setObjectReturn(true);
        $search->search();


        $dl = $search->getDocumentList();
        foreach ($dl as $element) {
            /** @var \SmartStructure\Hubconfiguration $element */
            if (!$element->name) {
                $err = $element->setLogicalName(sprintf("HUBELT_%04d", $element->initid));
                if ($err) {
                    throw new Exception($err);
                }
            }

            switch ($element->fromname) {
                case "HUBCONFIGURATIONGENERIC":
                    $configComponent = new HubExportGenericComponent($element);
                    $configComponent->appendTo($components);
                    break;

                case "HUBCONFIGURATIONIDENTITY":
                    $configComponent = new HubExportIdentityComponent($element);
                    $configComponent->appendTo($components);
                    break;
                case "HUBCONFIGURATIONLOGOUT":
                    $configComponent = new HubExportLogoutComponent($element);
                    $configComponent->appendTo($components);
                    break;
                case "HUBCONFIGURATIONLABEL":
                    $configComponent = new HubExportLabelComponent($element);
                    $configComponent->appendTo($components);
                    break;
                case "ADMINPARAMETERSHUBCONFIGURATION":
                    $configComponent = new HubExportAdminParameterComponent($element);
                    $configComponent->appendTo($components);
                    break;
                case "HUBBUSINESSAPP":
                    $configComponent = new HubExportBusinessAppComponent($element);
                    $configComponent->appendTo($components);
                    break;
                default:
                    $configComponent = new HubExportComponent($element);
                    $configComponent->appendTo($components);
            }
        }


        return $components;
    }


    protected function getDescription()
    {
        $description = $this->cel("description");

        $this->addField(HubFields::hub_instanciation_icone, "icon", $description);

        $this->addFieldArrayTwoColumns(
            HubFields::hub_instance_title,
            "title",
            HubFields::hub_instance_language,
            "lang",
            $description
        );


        return $description;
    }

    protected function getSetting()
    {


        $setting = $this->cel("setting");


        $this->addField(HubFields::hub_instanciation_router_entry, "router-entry", $setting);


        $this->addFieldArrayTwoColumns(
            HubFields::hub_instance_jsasset,
            "js",
            HubFields::hub_instance_jsasset_type,
            "type",
            $setting
        );
        $this->addFieldArrayTwoColumns(
            HubFields::hub_instance_cssasset,
            "css",
            HubFields::hub_instance_cssasset_type,
            "type",
            $setting
        );


        $display = $this->cel("display", null, $setting);
        $this->addField(HubFields::hub_instanciation_dock_left, "dock-left", $display);
        $this->addField(HubFields::hub_instanciation_dock_top, "dock-top", $display);
        $this->addField(HubFields::hub_instanciation_dock_right, "dock-right", $display);
        $this->addField(HubFields::hub_instanciation_dock_bottom, "dock-bottom", $display);


        return $setting;
    }

    protected function getSecurity()
    {

        $security = $this->cel("security");


        $this->addField(HubFields::hub_access_roles, "access-role", $security);
        $this->addField(HubFields::hub_super_role, "super-role", $security);


        return $security;
    }
}
