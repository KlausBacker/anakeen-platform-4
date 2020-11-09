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
    public function getZip($outZipPath)
    {

        $domConfig = $this->initDom();
        $domConfig->appendChild($this->getInstance());

        $zip = new \ZipArchive();
        $zip->open($outZipPath, \ZipArchive::CREATE);
        $zip->addFromString(sprintf("100-hub-%s.xml", $this->smartElement->name), $this->dom->saveXML());

        $xmlData = $this->exportHubComponents($extraData);
        // print_r($extraData);print_r($xmlData);exit;


        $k = 200;
        foreach ($extraData as $name => $xmlComponent) {
            $zip->addFromString(sprintf("extra/%3d-%s.xml", $k++, $name), $xmlComponent);
        }

        $k = 300;
        foreach ($xmlData as $name => $xmlComponent) {
            $zip->addFromString(sprintf("components/%3d-%s.xml", $k++, $name), $xmlComponent);
        }

        $zip->close();
    }


    protected function getInstance()
    {
        $instance = $this->cel("instance");

        $instance->setAttribute("name", ExportConfiguration::getLogicalName($this->smartElement->id));
        $instance->appendChild($this->getDescription());
        $instance->appendChild($this->getSetting());
        $instance->appendChild($this->getSecurity());
        return $instance;
    }


    protected function exportHubComponents(&$extraData)
    {
        $extraData = [];
        $search = new SearchSmartData("", "HUBCONFIGURATION");
        $search->overrideViewControl();
        $search->addFilter("%s = '%s'", Fields::hub_station_id, $this->smartElement->initid);
        $search->setOrder(Fields::hub_docker_position . ',' . Fields::hub_order);
        $search->setObjectReturn(true);
        $search->search();


        $dl = $search->getDocumentList();

        $xmlData = [];
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
                    $xmlData[$element->name] = $configComponent->getXml();
                    break;
                case "HUBCONFIGURATIONIDENTITY":
                    $configComponent = new HubExportIdentityComponent($element);
                    $xmlData[$element->name] = $configComponent->getXml();
                    break;
                case "HUBCONFIGURATIONLOGOUT":
                    $configComponent = new HubExportLogoutComponent($element);
                    $xmlData[$element->name] = $configComponent->getXml();
                    break;
                case "HUBCONFIGURATIONLABEL":
                    $configComponent = new HubExportLabelComponent($element);
                    $xmlData[$element->name] = $configComponent->getXml();
                    break;
                case "ADMINPARAMETERSHUBCONFIGURATION":
                    $configComponent = new HubExportAdminParameterComponent($element);
                    $xmlData[$element->name] = $configComponent->getXml();
                    break;
                case "HUBBUSINESSAPP":
                    $configComponent = new HubExportBusinessAppComponent($element);
                    $xmlData[$element->name] = $configComponent->getXml();
                    $extraData = array_merge($extraData, $configComponent->getExtraXml());
                    break;
                default:
                    $configComponent = new HubExportComponent($element);
                    $xmlData[$element->name] = $configComponent->getXml();
            }
        }

        return $xmlData;
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
        $setting = $this->cel("settings");

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


        $docks = $this->cel("docks", null, $setting);
        $dock = $this->cel("dock-left");
        if ($this->addField(HubFields::hub_instanciation_dock_left, "collapse", $dock)) {
            $docks->appendChild($dock);
        }
        $dock = $this->cel("dock-top");
        if ($this->addField(HubFields::hub_instanciation_dock_top, "collapse", $dock)) {
            $docks->appendChild($dock);
        }
        $dock = $this->cel("dock-right");
        if ($this->addField(HubFields::hub_instanciation_dock_right, "collapse", $dock)) {
            $docks->appendChild($dock);
        }
        $dock = $this->cel("dock-bottom");
        if ($this->addField(HubFields::hub_instanciation_dock_bottom, "collapse", $dock)) {
            $docks->appendChild($dock);
        }


        return $setting;
    }

    protected function getSecurity()
    {

        $security = $this->cel("security");

        $accessRoles = $this->cel("access-roles");
        $accessRoles->setAttribute("logical-operator", "or");

        if ($this->addField(HubFields::hub_access_roles, "access-role", $accessRoles)) {
            $security->appendChild($accessRoles);
        }
        $this->addField(HubFields::hub_super_role, "super-role", $security);


        return $security;
    }
}
