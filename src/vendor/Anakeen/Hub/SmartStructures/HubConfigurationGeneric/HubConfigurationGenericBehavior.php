<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationGeneric;

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
        $assets["css"][] = $this->resolveAssets("css");
        return $assets;
    }

    protected function getEntryOptions()
    {
        $options = parent::getEntryOptions();
        $options["libName"] = $this->getRawValue(HubConfigurationGenericFields::hge_component_libname);
        return $options;
    }

    protected function getComponentConfiguration()
    {
        return [
            "name" => $this->getRawValue(HubConfigurationGenericFields::hge_component_tag),
            "props" => json_decode($this->getRawValue("hge_component_props"), true)
        ];
    }

    protected function resolveAssets($type)
    {
        $prefix = sprintf("hge_%s", $type);
        $assets = $this->getArrayRawValues($prefix."assets");
        if (!empty($assets)) {
            return array_filter(array_map(function ($item) use ($prefix, $type) {
                $assetType = $item[$prefix."asset_type"];
                $assetPath = $item[$prefix."asset"];
                if (empty($assetPath)) {
                    return null;
                }
                if ($assetType === "MANIFEST") {
                    $tokens = explode("#", $assetPath);
                    $manifestPath = preg_replace("/\/\//", "/", PUBLIC_DIR."/".$tokens[0]);
                    if (!file_exists($manifestPath)) {
                        throw new Exception("HUB0001", $manifestPath);
                    }
                    if (!empty($tokens)) {
                        $manifest = json_decode(file_get_contents($manifestPath), true);
                        if (!empty($tokens[1])) {
                            return $manifest[$tokens[1]][$type];
                        } else {
                            return $manifest[$type];
                        }
                    }
                    return null;
                } else {
                    if (!file_exists($assetPath)) {
                        throw new Exception("HUB0001", $assetPath);
                    }
                    return $assetPath;
                }
            }, $assets));
        }
        return [];
    }
}
