<?php

namespace Dcp\Router;

use \Dcp\Core\ContextManager;
use \Dcp\Core\Exception;

class RouterLib
{

    /**
     * @var RouterConfig
     */
    protected static $config = null;

    /**
     * @return RouterConfig
     * @throws Exception
     */
    public static function getRouterConfig()
    {
        if (self::$config) {
            return self::$config;
        }

        $dir = ContextManager::getRootDirectory() . "/" . \Dcp\Core\Settings::RouterConfigDir;
        if ($handle = opendir($dir)) {
            $config = [];

            while (false !== ($entry = readdir($handle))) {
                if (preg_match("/\\.json$/", $entry)) {
                    $content = file_get_contents($dir . "/" . $entry);
                    $conf = json_decode($content, true);

                    if ($conf === null) {
                        throw new Exception("CORE0019", $dir . "/" . $entry);
                    }
                    $config = array_merge_recursive($config, $conf);
                }
            }


            closedir($handle);

            self::$config = json_decode(json_encode($config));
            return self::$config;
        } else {
            throw new Exception("CORE0020", $dir);
        }
    }

    /**
     * @param string $name
     *
     * @return RouterInfo|null
     * @throws Exception
     */
    public static function getRouteInfo($name)
    {
        $config = self::getRouterConfig();
        foreach ($config->routes as $route) {
            if ($route->name === $name) {
                $vars = get_object_vars($route);
                $info = new RouterInfo();
                foreach ($vars as $k => $v) {
                    $info->$k = $v;
                }
                return $info;
            }
        }
        return null;
    }

    public static function parseInfoToRegExp(array $parseInfos)
    {
        $delimiteur = "@";
        $regExps = [];
        foreach ($parseInfos as $parseInfo) {
            $regExp = $delimiteur;
            foreach ($parseInfo as $parsePart) {
                // print_r($parsePart);
                if (is_string($parsePart)) {
                    $regExp .= preg_quote($parsePart, $delimiteur);
                } elseif (is_array($parsePart)) {
                    //(?P<digit>\d+)
                    $regExp .= sprintf("(?P<%s>%s)", $parsePart[0], $parsePart[1]);
                }
            }
            $regExp .= $delimiteur;
            $regExps[] = $regExp;
        }
        return $regExps;
    }
}

class RouterConfig
{
    /**
     * @var RouterInfo[]
     */
    public $routes;
    /**
     * @var RouterInfo[]
     */
    public $middlewares;
}

class RouterInfo
{
    public $priority;
    /**
     * @var \Callable
     */
    public $callable;
    public $pattern;
    public $description;
    public $name;
    public $methods = [];
    public $authenticated = true;
}