<?php

namespace Anakeen\Router;

use \Anakeen\Core\ContextManager;
use \Dcp\Core\Exception;
use \Anakeen\Router\Config\RouterInfo;

class RouterLib
{

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
    const NS = "sde";

    public static function getRouterConfig()
    {
        if (self::$config) {
            return self::$config;
        }

        $dir = ContextManager::getRootDirectory() . "/" . \Anakeen\Core\Settings::RouterConfigDir;

        $configFiles = scandir($dir);
        if (is_array($configFiles)) {
            $config = [];
            foreach ($configFiles as $configFile) {
                if (preg_match("/\\.xml/", $configFile)) {
                    $conf = self::xmlDecode($dir . "/" . $configFile);

                    if ($conf === null) {
                        throw new Exception("CORE0019", $dir . "/" . $configFile);
                    }
                    $conf = self::normalizeConfig($conf, $configFile);
                    $config = array_merge_recursive($config, $conf);
                }
            }

            $config = json_decode(json_encode($config));

            self::$config = new RouterConfig($config);
            return self::$config;
        } else {
            throw new Exception("CORE0020", $dir);
        }
    }


    protected static function xmlDecode($configFile)
    {
        $xmlData = file_get_contents($configFile);

        $simpleData = simplexml_load_string($xmlData, \SimpleXMLElement::class, 0, self::NS, true);

        if ($simpleData === false) {
            throw new \Anakeen\Router\Exception("ROUTER0107", $configFile);
        }
        $data = [];
        foreach (["routes", "accesses", "middlewares", "parameters"] as $topNode) {
            $data[$topNode] = self::normalizeData($simpleData[0], $topNode);
        }

        return $data;
    }

    protected static function normalizeData(\SimpleXMLElement $data, $tag)
    {
        $node = ($data->$tag);


        $rawData = [];
        foreach ($node as $firstNode) {
            $nodeAttrs = $firstNode->attributes();
            $ns = "";
            foreach ($nodeAttrs as $iAttr => $vAttr) {
                if ($iAttr === "namespace") {
                    $ns = (string)$vAttr;
                }
            }
            foreach ($firstNode as $subNode) {
                $nodeAttrs = $subNode->attributes();
                $name = "";
                foreach ($nodeAttrs as $iAttr => $vAttr) {
                    if ($iAttr === "name") {
                        $name = (string)$vAttr;
                    }
                }
                $key = ($ns) ? ($ns . "::" . $name) : $name;

                foreach ($subNode as $tagName => $tagValue) {
                    if ($tagName === "method") {
                        $rawData[$key]["methods"][] = (string)$tagValue;
                    } elseif ($tagName === "pattern") {
                        if (isset($rawData[$key]["pattern"])) {
                            if (!is_array($rawData[$key][$tagName])) {
                                $rawData[$key][$tagName] = [$rawData[$key][$tagName]];
                            }
                            $rawData[$key][$tagName][] = (string)$tagValue;
                        } else {
                            $rawData[$key][$tagName] = (string)$tagValue;
                        }
                    } else {
                        if (!empty($tagValue->access)) {
                            /** @noinspection PhpUndefinedFieldInspection */
                            $operator = (string)$tagValue->attributes()->operator;
                            if (!$operator) {
                                $operator = "and";
                            }
                            /** @noinspection PhpUndefinedFieldInspection */
                            foreach ($tagValue->access as $accessValue) {
                                $rawData[$key][$tagName][$operator][] = (string)$accessValue;
                            }
                        } else {
                            $rawData[$key][$tagName] = (string)$tagValue;
                            if ($rawData[$key][$tagName] === "true") {
                                $rawData[$key][$tagName] = true;
                            } elseif ($rawData[$key][$tagName] === "false") {
                                $rawData[$key][$tagName] = false;
                            } elseif (is_numeric($rawData[$key][$tagName])) {
                                $rawData[$key][$tagName] = intval($rawData[$key][$tagName]);
                            }
                            foreach ($nodeAttrs as $iAttr => $vAttr) {
                                if ($iAttr !== "name") {
                                    $rawData[$key][$iAttr] = (string)$vAttr;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $rawData;
    }

    protected static function normalizeConfig(array $config, $configFileName)
    {

        if (!empty($config["routes"])) {
            $routes = $config["routes"];
            $nr = [];
            foreach ($routes as $routeName => $route) {
                $route["name"] = $routeName;
                $route["configFile"] = $configFileName;
                if (!isset($route["priority"])) {
                    $route["priority"] = 0;
                }
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
                if (!isset($middle["priority"])) {
                    $middle["priority"] = 0;
                }
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


        if (!empty($config["parameters"])) {
            $params = $config["parameters"];
            $nr = [];
            foreach ($params as $name => $param) {
                $param["name"] = $name;
                $param["configFile"] = $configFileName;
                $nr[] = $param;
            }
            $config["parameters"] = $nr;
        }

        return $config;
    }


    /**
     * Get route configuration for a named route
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
            $regExp = $delimiteur . '^';
            foreach ($parseInfo as $parsePart) {
                if (is_string($parsePart)) {
                    $regExp .= preg_quote($parsePart, $delimiteur);
                } elseif (is_array($parsePart)) {
                    //(?P<digit>\d+)
                    $regExp .= sprintf("(?P<%s>%s)", $parsePart[0], $parsePart[1]);
                }
            }
            $regExp .= '$' . $delimiteur;
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
