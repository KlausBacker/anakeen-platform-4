<?php

namespace Anakeen\Components\Grid;

use Anakeen\Components\Grid\Exceptions\Exception;
use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\ContextParameterManager;
use DOMDocument;
use DOMXPath;

class SmartGridControllerParameter
{
    const NS = "grid";
    const NSURL = "https://platform.anakeen.com/4/schemas/grid/1.0";

    protected const PARAMETER_NS = "Ui";
    protected const SE_GRID_CONTROLLERS = "SE_GRID_CONTROLLERS";
    protected const DEFAULT_CONTROLLER = [
        "name" => "DEFAULT_GRID_CONTROLLER",
        "class" => "Anakeen\Components\Grid\DefaultGridController"
    ];

    public static function addConfiguration($path)
    {
        static::deleteConfig($path);
        $configs = static::parseFileConfiguration($path);
        if (!empty($configs)) {
            static::addParameterConfig(...$configs);
        }
    }

    public static function deleteConfig($file)
    {
        $gridControllers = self::getParameterValue();
        $result = array_filter($gridControllers, function ($item) use ($file) {
            return $item["file"] !== $file;
        });
        static::setParameterValue($result);
    }

    public static function deleteAllConfig()
    {
        static::setParameterValue([]);
    }

    public static function listAllConfig()
    {
        $configs = static::getParameterValue();
        print "\n";
        foreach ($configs as $config) {
            print "Grid controller configuration file: " . $config["file"] . "\n";
            print "--8<--\n" . print_r($config, true) . "\n-->8--\n\n";
        }
        print "\n---------------\n";
        if (is_array($configs)) {
            print count($configs) . " grid controller registrations\n";
        }


        return true;
    }

    protected static function parseFileConfiguration(string $filepath)
    {
        $absPath = ContextManager::getRootDirectory() . "/" . $filepath;

        $doc = new DOMDocument();
        $success = $doc->load($absPath);
        if (!$success) {
            throw new Exception("GRID0007", $absPath);
        }
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace("x", self::NSURL);
        $query = "/x:controllers/x:controller";
        $result = $xpath->query($query);
        $gridControllers = [];
        foreach ($result as $node) {
            $class = $node->getAttribute("class");
            $name = $node->getAttribute("name");

            $gridControllers[] = [
                "class" => $class,
                "file" => $filepath,
                "name" => $name
            ];
        }
        return $gridControllers;
    }


    public static function getControllerByName($controllerName)
    {
        $result = null;

        if ($controllerName === static::DEFAULT_CONTROLLER["name"]) {
            return static::DEFAULT_CONTROLLER;
        }

        $controllers = static::getParameterValue();
        $found = array_search($controllerName, array_column($controllers, "name"));
        if ($found !== false) {
            $result = $controllers[$found];
        }
        return $result;
    }

    public static function getParameterValue($defaultValue = [])
    {
        $parameterValue = ContextParameterManager::getValue(self::PARAMETER_NS, self::SE_GRID_CONTROLLERS, json_encode($defaultValue));
        if ($parameterValue) {
            $values = json_decode($parameterValue, true);
            if ($values && is_array($values)) {
                return array_values($values);
            }
        }
        return $defaultValue;
    }

    protected static function setParameterValue($config)
    {
        ContextParameterManager::setValue("Ui", "SE_GRID_CONTROLLERS", json_encode($config));
    }

    protected static function addParameterConfig(...$configs)
    {
        $gridControllers = static::getParameterValue();
        foreach ($configs as $config) {
            if (empty($config["class"])) {
                throw new Exception("GRID0013");
            }
            if (empty($config["name"])) {
                throw new Exception("GRID0008");
            }
            if (!empty(static::getControllerByName($config["name"]))) {
                throw new Exception("GRID0012", $config["name"]);
            }
            $gridControllers[] = $config;
        }
        static::setParameterValue($gridControllers);
    }
}
