<?php
/**
 * Created by PhpStorm.
 * User: Charles
 * Date: 15/12/2017
 * Time: 10:27
 */

namespace Dcp\Ui;


/**
 * Class DocumentGetAssetPath
 *
 * Get the path of documents and associated elements
 *
 * @package Dcp\Ui
 */
class UIGetAssetPath
{

    protected static $assetPath = 'uiAssets/externals/';
    protected static $widgetPath = 'uiAssets/widgets/';
    protected static $inDebug = null;
    protected static $assetPaths = null;
    protected static $widgetPaths = null;
    protected static $ws = null;

    public static function isInDebug() {
        if (self::$inDebug === null) {
            $modeDebug = \ApplicationParameterManager::getParameterValue("DOCUMENT", "MODE_DEBUG");
            self::$inDebug = $modeDebug !== "FALSE";
        }
        return self::$inDebug;
    }

    protected static function getAssetsPaths() {
        if (self::$assetPaths === null) {
            self::$assetPaths = json_decode(file_get_contents(self::$assetPath."/externalAssets.json"), true);
        }
        return self::$assetPaths;
    }

    protected static function getWidgetPath() {
        if (self::$widgetPaths === null) {
            $lastPart = self::isInDebug() ? "/debug/" : "/prod/";
            error_log(self::$widgetPath."/".$lastPart."/smartElement.json");
            self::$widgetPaths = json_decode(file_get_contents(self::$widgetPath."/".$lastPart."/smartElement.json"), true);
        }
        return self::$widgetPaths;
    }

    public static function getWs() {
        if (self::$ws === null) {
            self::$ws = $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        }
        return self::$ws;
    }

    public static function getJSJqueryPath() {
        $jqueryFileName = self::isInDebug() ? 'jquery.js' : 'jquery.min.js';
        return self::$assetPath.'/jquery/'.$jqueryFileName.'?ws='.self::getWs();
    }

    public static function getJSKendoPath() {
        if (self::isInDebug()) {
            return self::$assetPath.'/KendoUI/KendoUI.js?ws='.self::getWs();
        } else {
            $asset = self::getAssetsPaths();
            return $asset["KendoUI"]["js"];
        }
    }

    public static function getJSSmartElementPath() {
        $paths = self::getWidgetPath();
        error_log(var_export($paths, true));
        return $paths["smartElement"]["js"];
    }

    public static function getJSSmartElementGridPath() {
        $paths = self::getWidgetPath();
        return $paths["smartElementGrid"]["js"];
    }

    public static function getSmartElement() {
        $elements = [
            "js" => [
                self::getJSJqueryPath(),
                self::getJSSmartElementPath()
                ],
            "css" => []
        ];
        error_log(var_export($elements, true));
        return $elements;
    }

    public static function getSmartElementGrid() {
        $elements = [
            "js" => [
                self::getJSJqueryPath(),
                self::getJSKendoPath(),
                self::getJSSmartElementGridPath()
            ],
            "css" => []
        ];
        return $elements;
    }

}