<?php

namespace Anakeen\Router;

use \Dcp\Core\ContextManager;
use \Dcp\Core\Exception;

class RouterLib
{

    const cacheFile="config.cache";
    /**
     * @var RouterConfig
     */
    protected static $config = null;

    /**
     * Extract configuration from config files included in "config" directory
     *
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
                    $conf = self::normalizeConfig($conf, $entry);
                    $config = array_merge_recursive($config, $conf);
                }
            }

            closedir($handle);

            $config = json_decode(json_encode($config));

            self::$config = new RouterConfig($config);
            return self::$config;
        } else {
            throw new Exception("CORE0020", $dir);
        }
    }

    protected static function normalizeConfig(array $config, $configFileName)
    {

        if (!empty($config["routes"])) {
            $routes = $config["routes"];
            $nr = [];
            foreach ($routes as $routeName => $route) {
                $route["name"] = $routeName;
                $route["configFile"] = $configFileName;
                $nr[] = $route;
            }
            $config["routes"] = $nr;
        }

        if (!empty($config["middlewares"])) {
            $middles = $config["middlewares"];
            $nr = [];
            foreach ($middles as $name => $middle) {
                $middle["name"] = $name;
                $middle["configFile"] = $configFileName;
                $nr[] = $middle;
            }
            $config["middlewares"] = $nr;
        }


        if (!empty($config["apps"])) {
            $apps = $config["apps"];
            $nr = [];
            foreach ($apps as $name => $app) {
                $app["name"] = $name;
                $app["configFile"] = $configFileName;
                $nr[] = $app;
            }
            $config["apps"] = $nr;
        }


        if (!empty($config["accesses"])) {
            $acls = $config["accesses"];
            $nr = [];
            foreach ($acls as $name => $acl) {
                $acl["name"] = $name;
                $acl["configFile"] = $configFileName;
                $nr[] = $acl;
            }
            $config["accesses"] = $nr;
        }

        return $config;
    }


    /**
     * Get route configuration for a nemed route
     *
     * @param string $name route name
     *
     * @return RouterInfo|null
     * @throws Exception
     */
    public static function getRouteInfo($name)
    {
        $config = self::getRouterConfig();
        foreach ($config->getRoutes() as $route) {
            if ($route->name === $name) {
                $info = new RouterInfo($route);
                return $info;
            }
        }
        return null;
    }

    /**
     * Convert parse info into regexp to be used to match pattern routes
     *
     * @param array $parseInfos
     *
     * @see \FastRoute\RouteParser\Std::parse()
     * @see matchPattern()
     * @return array
     */
    public static function parseInfoToRegExp(array $parseInfos)
    {
        $delimiteur = "@";
        $regExps = [];
        foreach ($parseInfos as $parseInfo) {
            $regExp = $delimiteur.'^';
            foreach ($parseInfo as $parsePart) {
                // print_r($parsePart);
                if (is_string($parsePart)) {
                    $regExp .= preg_quote($parsePart, $delimiteur);
                } elseif (is_array($parsePart)) {
                    //(?P<digit>\d+)
                    $regExp .= sprintf("(?P<%s>%s)", $parsePart[0], $parsePart[1]);
                }
            }
            $regExp .= '$'.$delimiteur;
            $regExps[] = $regExp;
        }
        return $regExps;
    }

    /**
     * Verify if url match the pattern
     *
     * @param string $pattern route pattern configuration
     * @param string $url     request url
     *
     * @return bool true if match
     */
    public static function matchPattern($pattern, $url)
    {
        $sParser = new \FastRoute\RouteParser\Std;

        $patternInfos = $sParser->parse($pattern);
        $regExps = self::parseInfoToRegExp($patternInfos);
        foreach ($regExps as $regExp) {
            if (preg_match($regExp, $url)) {
                return true;
            }
        }
        return false;
    }
}
