<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationGeneric;

use Anakeen\Ui\UIGetAssetPath;

class HubAssetPath implements AssetPath
{

    public static function getJSPath($assetName, $entry)
    {
        return UIGetAssetPath::getElementAssets($assetName, UIGetAssetPath::isInDebug() ? "dev" : "prod")[$entry]["js"];
    }

    public static function getCSSPath($assetName, $entry)
    {
        return UIGetAssetPath::getElementAssets($assetName, UIGetAssetPath::isInDebug() ? "dev" : "prod")[$entry]["css"];
    }
}
