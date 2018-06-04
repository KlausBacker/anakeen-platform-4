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
                    $content = file_get_contents($dir . "/" . $configFile);

                    $conf = self::xmlDecode($content);

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


    protected static function xmlDecode($xmlData)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xmlData);

        $simpleData = simplexml_load_string($xmlData, \SimpleXMLElement::class, 0, "router", true);

        $data = [];
        foreach (["routes", "apps", "accesses", "middlewares", "parameters"] as $topNode) {
            $data[$topNode] = self::normalizeData($simpleData[0], $topNode);
        }

        return $data;
    }

    protected static function normalizeData(\SimpleXMLElement $data, $tag)
    {
        $node = ($data->$tag);

        $rawData = [];
        foreach ($node as $firstNode) {
            foreach ($firstNode as $subNode) {
                $nodeAttrs = $subNode->attributes();
                $name = "";
                foreach ($nodeAttrs as $iAttr => $vAttr) {
                    if ($iAttr === "name") {
                        $name = (string)$vAttr;
                    }
                }

                foreach ($subNode as $tagName => $tagValue) {
                    $rawValue = get_object_vars($tagValue);
                    if (count($rawValue) === 0) {
                        if ($tagName === "method") {
                            $rawData[$name]["methods"][] = (string)$tagValue;
                        } elseif ($tagName === "pattern") {
                            if (isset($rawData[$name]["pattern"])) {
                                if (! is_array($rawData[$name][$tagName])) {
                                    $rawData[$name][$tagName] = [$rawData[$name][$tagName]];
                                }
                                $rawData[$name][$tagName][] = (string)$tagValue;
                            } else {
                                $rawData[$name][$tagName] = (string)$tagValue;
                            }
                        } else {
                            $rawData[$name][$tagName] = (string)$tagValue;
                            if ($rawData[$name][$tagName] === "true") {
                                $rawData[$name][$tagName] = true;
                            } elseif ($rawData[$name][$tagName] === "false") {
                                $rawData[$name][$tagName] = false;
                            } elseif (is_numeric($rawData[$name][$tagName])) {
                                 $rawData[$name][$tagName] = intval($rawData[$name][$tagName]);
                            }
                            foreach ($nodeAttrs as $iAttr => $vAttr) {
                                if ($iAttr !== "name") {
                                    $rawData[$name][$iAttr] = (string)$vAttr;
                                }
                            }
                        }
                    } else {
                        /** @noinspection PhpUndefinedFieldInspection */
                        $operator = (string)$tagValue->attributes()->operator;

                        if ($operator) {
                            foreach ($rawValue["access"] as $accessValue) {
                                $rawData[$name][$tagName][$operator][] = $accessValue;
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
