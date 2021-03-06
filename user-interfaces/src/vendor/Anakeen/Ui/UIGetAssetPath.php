<?php

namespace Anakeen\Ui;

use Anakeen\Core\ContextManager;

/**
 * Class DocumentGetAssetPath
 *
 * Get the path of documents and associated elements
 *
 * @package Anakeen\Ui
 */
class UIGetAssetPath
{

    protected static $assetPath = '/uiAssets/externals/';
    protected static $anakeenPublicPath = 'Anakeen/';
    protected static $anakeenManifestPath = __DIR__.'/../../../public/Anakeen/manifest';
    protected static $inDebug = null;
    protected static $ws = null;

    public static function isInDebug()
    {
        if (self::$inDebug === null) {
            $modeDebug = ContextManager::getParameterValue("Ui", "MODE_DEBUG");
            self::$inDebug = $modeDebug !== "FALSE";
        }
        return self::$inDebug;
    }

    public static function getWs()
    {
        if (self::$ws === null) {
            self::$ws = $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        }
        return self::$ws;
    }

    /**
     * Get the content of the manifest of a generated pack of asset
     *
     * @param $name
     * @param string $mode
     * @return mixed
     * @throws Exception
     */
    public static function getElementAssets($name, $mode = "prod")
    {
        $manifestPath = self::$anakeenManifestPath.DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.$mode.".json";
        if (!file_exists($manifestPath)) {
            throw new Exception("UI0400", $name, $manifestPath);
        }
        $manifest = json_decode(file_get_contents($manifestPath), true);
        return $manifest;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function getJSKendoPath()
    {
        $assets = self::getElementAssets("assets", self::isInDebug()? "dev":"prod");
        return $assets["KendoUI"]["js"];
    }

    public static function getCssKendo()
    {
        if (self::isInDebug()) {
            $paths = self::getElementAssets("theme", "dev");
        } else {
            $paths = self::getElementAssets("theme");
        }
        return $paths["kendo"]["css"];
    }

    /**
     * Add the VERSION query parameter to a custom asset path
     * @param $assetPath
     * @return string
     */
    public static function getCustomAssetPath($assetPath)
    {
        $baseUrl = $assetPath;
        $parsedQuery = parse_url($baseUrl, PHP_URL_QUERY);
        if (!empty($parsedQuery)) {
            $baseUrl .= '&';
        } else {
            $baseUrl .= '?';
        }
        $baseUrl .= "ws=".self::getWs();

        return $baseUrl;
    }

    /**
     * Return the asset ank web components path. By default, the route path is returned.
     * @return string - the asset path
     * @throws Exception
     */
    public static function getSmartWebComponentsPath()
    {
        $name = "ank-components";
        if (self::isInDebug()) {
            $paths = self::getElementAssets("ank-components", "dev");
        } else {
            $paths = self::getElementAssets("ank-components", "prod");
        }
        return $paths[$name]["js"];
    }

    public static function getCssSmartWebComponents()
    {
        return self::getCss("components");
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function getJSSmartElementPath()
    {
        $name = "smartElement";
        if (self::isInDebug()) {
            $paths = self::getElementAssets("smartElement", "dev");
        } else {
            $paths = self::getElementAssets("smartElement", "prod");
        }
        return $paths[$name]["js"];
    }

    public static function getCssSmartElement()
    {
        return self::getCss("smartElement");
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function getJSSmartElementWidgetPath()
    {
        $name = "smartElementWidget";
        if (self::isInDebug()) {
            $paths = self::getElementAssets("smartElement", "dev");
        } else {
            $paths = self::getElementAssets("smartElement", "prod");
        }
        return $paths[$name]["js"];
    }

    public static function getCssBootstrap()
    {
        return self::getCss("bootstrap");
    }

    public static function getCssCkeditor()
    {
        return self::getCss("kendo");
    }

    public static function getCss($moduleName)
    {
        if (self::isInDebug()) {
            $paths = self::getElementAssets("theme", "dev");
        } else {
            $paths = self::getElementAssets("theme");
        }
        if (!isset($paths[$moduleName]) || ! isset($paths[$moduleName]["css"])) {
            throw new Exception("UI0401", $moduleName);
        }
        return $paths[$moduleName]["css"];
    }
}
