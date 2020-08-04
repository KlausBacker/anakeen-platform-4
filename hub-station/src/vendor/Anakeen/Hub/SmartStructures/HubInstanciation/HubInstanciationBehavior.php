<?php

namespace Anakeen\Hub\SmartStructures\HubInstanciation;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SmartStructure\Callables\InputArgument;
use Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod;
use Anakeen\Exception;
use Anakeen\Hub\SmartStructures\HubConfigurationGeneric\AssetPath;
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
                if (strpos(
                    $currentLanguage,
                    "fr_FR"
                ) !== false && $title[HubinstanciationFields::hub_instance_language] === "Français") {
                    $this->title = $title[HubinstanciationFields::hub_instance_title];
                } elseif (strpos(
                    $currentLanguage,
                    "en_US"
                ) !== false && $title[HubinstanciationFields::hub_instance_language] === "English") {
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
                "/hub/station/" . $this->getRawValue(HubinstanciationFields::instance_logical_name) . "/"
            ),
            "dockConfiguration" => array(
                "left" => $this->getRawValue(HubinstanciationFields::hub_instanciation_dock_left),
                "right" => $this->getRawValue(HubinstanciationFields::hub_instanciation_dock_right),
                "top" => $this->getRawValue(HubinstanciationFields::hub_instanciation_dock_top),
                "bottom" => $this->getRawValue(HubinstanciationFields::hub_instanciation_dock_bottom)
            ),
            "globalAssets" => [
                "js" => $this->resolveAssets("js"),
                "css" => $this->resolveAssets("css")
            ]
        ];
    }

    protected function resolveAssets($type)
    {
        $prefix = sprintf("hub_instance_%s", $type);
        $assets = $this->getArrayRawValues($prefix . "assets");

        if (!empty($assets)) {
            return array_filter(array_map(function ($item) use ($prefix, $type) {
                $assetType = $item[$prefix . "asset_type"];
                $assetPath = $item[$prefix . "asset"];
                if (empty($assetPath)) {
                    return null;
                }
                if ($assetType === "manifest") {
                    $err = $this->checkAssetCallable($assetType, $assetPath);
                    if (!empty($err)) {
                        $exception = new Exception("HUB0005", $assetPath);
                        $exception->setUserMessage($err);
                        throw $exception;
                    }
                    $parseMethod = new ParseFamilyMethod();
                    $parsed = $parseMethod->parse($assetPath);
                    $args = array_map(function (InputArgument $input) {
                        return $input->name;
                    }, $parsed->inputs);
                    $result = forward_static_call(
                        sprintf("%s::%s", $parsed->className, $parsed->methodName),
                        ...$args
                    );
                    if (!$result) {
                        throw new Exception("HUB0003", $assetPath);
                    } else {
                        return $result;
                    }
                } else {
                    if (!file_exists(PUBLIC_DIR . "/" . $assetPath)) {
                        throw new Exception("HUB0004", PUBLIC_DIR . "/" . $assetPath);
                    }
                    return $assetPath;
                }
            }, $assets));
        }
        return [];
    }

    public function checkAssetCallable($assetType, $assetValue)
    {
        if ($assetType === "manifest") {
            $parseMethod = new ParseFamilyMethod();
            $parsed = $parseMethod->parse($assetValue);
            if (empty($parsed->className) || !is_subclass_of($parsed->className, AssetPath::class)) {
                return ___(
                    sprintf("The callable must be a static method of a class
                 that implements Anakeen\Hub\SmartStructures\HubConfigurationGeneric\AssetPath"),
                    "HubConfigurationGenericBehavior"
                );
            }
            return "";
        }
        return "";
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
