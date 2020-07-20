<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationGeneric;

use Anakeen\Core\SmartStructure\Callables\InputArgument;
use Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod;
use Anakeen\Exception;
use Anakeen\SmartHooks;
use SmartStructure\Fields\Hubconfigurationgeneric as HubConfigurationGenericFields;

class HubConfigurationGenericBehavior extends \SmartStructure\Hubconfigurationvue
{

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->removeListeners(SmartHooks::PRESTORE);
    }

    protected function getAssets()
    {
        $assets = parent::getAssets();
        $assets["js"][] = $this->resolveAssets("js");
        $assetcss = $this->resolveAssets("css");
        if ($assetcss) {
            $assets["css"][] = $assetcss;
        }
        return $assets;
    }

    protected function getComponentConfiguration()
    {
        return [
            "name" => $this->getRawValue(HubConfigurationGenericFields::hge_component_tag),
            "props" => json_decode($this->getRawValue("hge_component_props"), true)
        ];
    }

    public function checkAssetCallable($assetType, $assetValue)
    {
        if ($assetType === "MANIFEST") {
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

    protected function resolveAssets($type)
    {
        $prefix = sprintf("hge_%s", $type);
        $assets = $this->getArrayRawValues($prefix . "assets");
        if (!empty($assets)) {
            return array_filter(array_map(function ($item) use ($prefix, $type) {
                $assetType = $item[$prefix . "asset_type"];
                $assetPath = $item[$prefix . "asset"];
                if (empty($assetPath)) {
                    return null;
                }
                if ($assetType === "MANIFEST") {
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
                        throw new Exception("HUB0001", $assetPath);
                    } else {
                        return $result;
                    }
                } else {
                    if (!file_exists(PUBLIC_DIR . "/" . $assetPath)) {
                        throw new Exception("HUB0001", PUBLIC_DIR . "/" . $assetPath);
                    }
                    return $assetPath;
                }
            }, $assets));
        }
        return [];
    }
}
