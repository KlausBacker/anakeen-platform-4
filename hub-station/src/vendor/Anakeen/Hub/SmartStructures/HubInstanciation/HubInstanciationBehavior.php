<?php

namespace Anakeen\Hub\SmartStructures\HubInstanciation;

use Anakeen\Core\ContextManager;
use Anakeen\Exception;
use Anakeen\SmartHooks;
use SmartStructure\Fields\Hubinstanciation as HubinstanciationFields;

class HubInstanciationBehavior extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(
            SmartHooks::PRESTORE,
            function () {
                $this->getFavIcon();
            }
        )->addListener(
            SmartHooks::POSTSTORE,
            function () {
                $this->affectLogicalName();
            }
        )->addListener(
            SmartHooks::PREIMPORT,
            function () {
                $this->getFavIcon();
            }
        );
    }

    public function getCustomTitle()
    {
        $titles = $this->getArrayRawValues(HubinstanciationFields::hub_instance_titles);
        $currentLanguage = ContextManager::getLanguage();
        if ($titles) {
            foreach ($titles as $title) {
                if (strpos($currentLanguage, "fr_FR") !== false && $title[HubinstanciationFields::hub_instance_language] === "Français") {
                    $this->title = $title[HubinstanciationFields::hub_instance_title];
                } elseif (strpos($currentLanguage, "en_US") !== false && $title[HubinstanciationFields::hub_instance_language] === "English") {
                    $this->title = $title[HubinstanciationFields::hub_instance_title];
                }
            }
        }
        return $this->title;
    }

    public function getFavIcon()
    {
        $icon = $this->icon;
        $newIcon = $this->getRawValue(HubinstanciationFields::hub_instanciation_icone);
        if ($newIcon) {
            $icon = $newIcon;
            $this->icon = $newIcon;
            return $icon;
        }
        return $icon;
    }

    protected function affectLogicalName()
    {
        $instanceName = $this->getRawValue(HubinstanciationFields::instance_logical_name);
        if ($this->name !== $instanceName) {
            $err = $this->setLogicalName($instanceName, true, true);
            if ($err) {
                throw new Exception($err);
            } else {
                $err = $this->setLogicalName($instanceName, true);
                if ($err) {
                    throw new Exception($err);
                }
            }
        }
    }

    public function getConfiguration()
    {
        return [
            "instanceName" => $this->getRawValue(HubinstanciationFields::instance_logical_name),
            "routerEntry" => $this->getRawValue(
                HubinstanciationFields::hub_instanciation_router_entry,
                "/hub/station/".$this->getRawValue(HubinstanciationFields::instance_logical_name)."/"
            ),
            "globalAssets" => [
                "js" => $this->getAttributeValue(HubinstanciationFields::hub_instance_jsasset),
                "css" => $this->getAttributeValue(HubinstanciationFields::hub_instance_cssasset)
            ]
        ];
    }

    public function checkLogicalName($logicalName)
    {
        if ($logicalName === $this->name) {
            return "";
        }
        $err = $this->setLogicalName($logicalName, false, true);
        if (!empty($err)) {
            return $err;
        }
        return "";
    }

    public function getDefaultLanguages()
    {
        return [
            [
                HubinstanciationFields::hub_instance_title => "",
                HubinstanciationFields::hub_instance_language => "English"
            ],
            [
                HubinstanciationFields::hub_instance_title => "",
                HubinstanciationFields::hub_instance_language => "Français"
            ]
        ];
    }
}
