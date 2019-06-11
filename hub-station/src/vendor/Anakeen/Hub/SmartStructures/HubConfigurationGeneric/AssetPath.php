<?php


namespace Anakeen\Hub\SmartStructures\HubConfigurationGeneric;

interface AssetPath
{
    public static function getJSPath($assetName, $entry);
    public static function getCSSPath($assetName, $entry);
}
