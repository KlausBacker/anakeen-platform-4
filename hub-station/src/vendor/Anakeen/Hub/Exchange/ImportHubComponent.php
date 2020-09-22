<?php


namespace Anakeen\Hub\Exchange;

use Anakeen\AdminCenter\Exchange\HubExportAdminParameterComponent;
use Anakeen\AdminCenter\Exchange\ImportHubComponentAdminParameters;
use Anakeen\BusinessApp\Exchange\HubExportBusinessAppComponent;
use Anakeen\BusinessApp\Exchange\ImportHubComponentBusinessApp;
use Anakeen\Core\SEManager;
use Anakeen\Exception;
use SmartStructure\Fields\Hubconfiguration as ComponentFields;

class ImportHubComponent extends ImportHubConfiguration
{


    public function importHubComponent(\DOMElement $component)
    {
        list($prefix, $tagName) = explode(":", $component->tagName);

        if ($tagName !== "component") {
            throw new Exception(sprintf("unexpected component tag \"%s\"", $component->tagName));
        }

        $ns = $component->lookupNamespaceUri($prefix);
        $componentStructure = null;
        switch ($ns) {
            case HubExportGenericComponent::$nsUrl:
                $componentStructure = SEManager::getFamily(\SmartStructure\Hubconfigurationgeneric::familyName);
                $importObject = new ImportHubComponentGeneric();
                break;
            case HubExportIdentityComponent::$nsUrl:
                $componentStructure = SEManager::getFamily(\SmartStructure\Hubconfigurationidentity::familyName);
                $importObject = new ImportHubComponentIdentity();
                break;
            case HubExportLabelComponent::$nsUrl:
                $componentStructure = SEManager::getFamily(\SmartStructure\Hubconfigurationlabel::familyName);
                $importObject = new ImportHubComponentLabel();
                break;
            case HubExportLogoutComponent::$nsUrl:
                $componentStructure = SEManager::getFamily(\SmartStructure\Hubconfigurationlogout::familyName);
                $importObject = new ImportHubComponentLogout();
                break;
            case HubExportAdminParameterComponent::$nsUrl:
                $componentStructure = SEManager::getFamily(\SmartStructure\Adminparametershubconfiguration::familyName);
                $importObject = new ImportHubComponentAdminParameters();
                break;
            default:
                if (class_exists(HubExportBusinessAppComponent::class)) {
                    if ($ns === HubExportBusinessAppComponent::$nsUrl) {
                        $componentStructure = SEManager::getFamily(\SmartStructure\Hubbusinessapp::familyName);
                        $importObject = new ImportHubComponentBusinessApp();
                    }
                }
        }
        if (!$componentStructure) {
            throw new Exception(sprintf("Unexpected component tag \"%s\"", $component->tagName));
        }
        $data = $importObject->getHubComponentData($component, $componentStructure);
        $otherData = $importObject->getCustomParameters($component, $componentStructure);
        $data[0] = array_merge($data[0], array_splice($otherData[0], 4));
        $data[1] = array_merge($data[1], array_splice($otherData[1], 4));

        return $data;
    }

    protected function getCustomParameters(\DOMElement $component, $componentStructure)
    {
        $mapping = $this->getCustomMapping();

        $parameter = $this->getNode($component, "parameters");
        $otherData = $this->applyMapping($parameter, $mapping, $componentStructure);
        return $otherData;
    }

    protected function getCustomMapping()
    {
        return [];
    }

    protected function getHubComponentData(\DOMElement $component, \Anakeen\Core\SmartStructure $componentStructure)
    {
        $this->dom = $component->ownerDocument;
        $this->smartNs = HubExportComponent::$NSHUBURLCOMPONENT;
        $parameter = $this->getNode($component, "parameters");
        $this->defaultNsPrefix = "hubc";

        $mapping = [
            "@instance-ref" => ComponentFields:: hub_station_id,
            "parameters/title" => ComponentFields::hub_title,
            "parameters/display/@position" => [
                ComponentFields::hub_docker_position,
                function ($v) use ($parameter) {
                    return strtoupper($v . "_" . $this->evaluateNs("display/@placement", $parameter));
                }
            ],
            "parameters/display/@order" => ComponentFields:: hub_order,
            "parameters/settings/@selectable" => [
                ComponentFields::hub_selectable,
                function ($v) {
                    return strtoupper($v);
                }
            ],
            "parameters/settings/@expandable" => [
                ComponentFields:: hub_expandable,
                function ($v) {
                    return strtoupper($v);
                }
            ],
            "parameters/settings/@activated" => [
                ComponentFields::hub_activated,
                function ($v) {
                    return strtoupper($v);
                }
            ],
            "parameters/settings/@activated-order" => ComponentFields::hub_activated_order,
            "parameters/security/visibility-roles/visibility-role/@login" => ComponentFields::hub_visibility_roles,
            "parameters/security/execution-roles/execution-role/@login" => ComponentFields:: hub_execution_roles
        ];


        return $this->applyMapping($component, $mapping, $componentStructure);
    }

    protected function getXPath($prefix)
    {
        $xpath = parent::getXPath($prefix);
        $xpath->registerNamespace(
            "hubc",
            HubExportComponent::$NSHUBURLCOMPONENT
        );
        return $xpath;
    }
}
