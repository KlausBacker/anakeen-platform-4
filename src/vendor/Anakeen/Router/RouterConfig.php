<?php

namespace Anakeen\Router;

use \Anakeen\Router\Config\AppInfo;
use \Anakeen\Router\Config\RouterInfo;

/**
 * Class RouterConfig
 * Extract configuration from config files included in "config" directory
 *
 * @package Anakeen\Router
 */
class RouterConfig
{
    /**
     * @var RouterInfo[]
     */
    protected $routes;
    /**
     * @var RouterInfo[]
     */
    protected $middlewares;

    /**
     * @var AppInfo[]
     */
    protected $apps;
    /**
     * @var Config\AccessInfo[]
     */
    protected $accesses;
    /**
     * @var Config\ParameterInfo[]
     */
    protected $parameters;

    public function __construct(\stdClass $data)
    {
        $this->middlewares = isset($data->middlewares) ? $data->middlewares : [];
        $this->routes = isset($data->routes) ? $data->routes : [];
        $this->apps = isset($data->apps) ? $data->apps : [];
        $this->accesses = isset($data->accesses) ? $data->accesses : [];
        $this->parameters = isset($data->parameters) ? $data->parameters : [];

        static::sortApps($this->apps);
        static::sortRoutesByPriority($this->routes);
        $this->uniqueName($this->routes);
        static::sortMiddleByPriority($this->middlewares);
        $this->uniqueName($this->middlewares);

        static::normalizeMethods($this->routes);
        static::normalizeMethods($this->middlewares);
    }

    protected function uniqueName(array &$routes)
    {
        /**
         * @var RouterInfo[] $uRoutes
         */
        $uRoutes = [];

        /**
         * @var RouterInfo $routeInfo
         */
        foreach ($routes as $routeInfo) {
            if (isset($uRoutes[$routeInfo->name])) {
                if (empty($routeInfo->override)) {
                    throw new Exception("ROUTES0128", $routeInfo->configFile, $routeInfo->name, $uRoutes[$routeInfo->name]->configFile);
                }
                if ($routeInfo->override === "partial") {
                    $routeInfo->configFile = $uRoutes[$routeInfo->name]->configFile . ', ' . $routeInfo->configFile;
                    $uRoutes[$routeInfo->name] = (object)array_merge(
                        (array)$uRoutes[$routeInfo->name],
                        (array)$routeInfo
                    );
                } elseif ($routeInfo->override === "complete") {
                    $uRoutes[$routeInfo->name] = $routeInfo;
                } else {
                    throw new Exception("ROUTES0129", $routeInfo->name);
                }
            } else {
                if (!empty($routeInfo->override)) {
                    throw new Exception("ROUTES0130", $routeInfo->name);
                }
                $uRoutes[$routeInfo->name] = $routeInfo;
            }
        }
        $routes = $uRoutes;
    }

    protected static function normalizeMethods(array &$routes)
    {
        foreach ($routes as &$route) {
            foreach ($route->methods as &$method) {
                $method = strtoupper($method);
            }
        }
    }

    protected static function sortApps(array &$apps)
    {
        usort($apps, function ($a, $b) {

            /**
             * @var AppInfo $a
             * @var AppInfo $b
             */

            if (!empty($a->override) && empty($b->override)) {
                return 1;
            }
            if (!empty($b->override) && empty($a->override)) {
                return -1;
            }
            if (!empty($a->parentName) && empty($b->parentName)) {
                return 1;
            }
            if (!empty($b->parentName) && empty($a->parentName)) {
                return -1;
            }


            return strcmp($a->name, $b->name);
        });
    }

    protected static function sortRoutesByPriority(array &$routes)
    {
        usort($routes, function ($a, $b) {

            /**
             * @var RouterInfo $a
             * @var RouterInfo $b
             */

            if (!empty($a->override) && empty($b->override)) {
                return 1;
            }
            if (!empty($b->override) && empty($a->override)) {
                return -1;
            }
            if (isset($a->priority) && isset($b->priority)) {
                if ($a->priority > $b->priority) {
                    return 1;
                }
                if ($a->priority < $b->priority) {
                    return -1;
                }
            }
            if (isset($a->priority) && !isset($b->priority)) {
                return 1;
            }
            if (!isset($a->priority) && isset($b->priority)) {
                return -1;
            }

            if (isset($a->pattern) && isset($b->pattern) && !is_array($a->pattern) && !is_array($b->pattern)) {
                if (strlen($a->pattern) > strlen($b->pattern)) {
                    return 1;
                }
                if (strlen($a->pattern) < strlen($b->pattern)) {
                    return -1;
                }
            }
            return strcmp($a->name, $b->name);
        });
    }

    protected static function sortMiddleByPriority(array &$middles)
    {
        self::sortRoutesByPriority($middles);
    }

    /**
     * @return RouterInfo[]
     */
    public function getRoutes()
    {
        $routes = [];
        foreach ($this->routes as $k => $v) {
            $routes[$k] = new RouterInfo($v);
        }

        return $routes;
    }

    /**
     * @return RouterInfo[]
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * @return AppInfo[]
     */
    public function getApps()
    {
        /**
         * @var AppInfo[] $appsInfo
         */
        $appsInfo = [];
        /**
         * @var AppInfo $appData
         */
        foreach ($this->apps as $appData) {
            if (isset($appsInfo[$appData->name])) {
                if (empty($appData->override)) {
                    throw new Exception("ROUTES0134", $appData->configFile, $appData->name, $appsInfo[$appData->name]->configFile);
                }

                if ($appData->override === "partial") {
                    $appData->configFile = $appsInfo[$appData->name]->configFile . ', ' . $appData->configFile;
                    $appsInfo[$appData->name]->set($appData);
                } else {
                    throw new Exception("ROUTES0135", $appData->name);
                }
            } else {
                $appsInfo[$appData->name] = new AppInfo($appData);
            }
        }

        return $appsInfo;
    }

    /**
     * @return Config\AccessInfo[]
     */
    public function getAccesses()
    {
        /**
         * @var Config\AccessInfo[]
         */
        $accessesInfo = [];
        foreach ($this->accesses as $appData) {
            if (isset($accessesInfo[$appData->name])) {
                throw new Exception("ROUTES0137", $appData->configFile, $appData->name, $accessesInfo[$appData->name]->configFile);
            }
            $accessesInfo[$appData->name] = new Config\AccessInfo($appData);
        }

        return $accessesInfo;
    }


    /**
     * @return Config\ParameterInfo[]
     */
    public function getParameters()
    {
        $paramInfo = [];
        foreach ($this->parameters as $appData) {
            if (isset($paramInfo[$appData->name])) {
                throw new Exception("ROUTES0138", $appData->configFile, $appData->name, $paramInfo[$appData->name]->configFile);
            }
            $paramInfo[$appData->name] = new Config\ParameterInfo($appData);
        }

        return $paramInfo;
    }

    /**
     * Record all application configuration in database
     */
    public function recordApps()
    {
        $apps = $this->getApps();
        foreach ($apps as $appInfo) {
            $appInfo->record();
        }
    }


    /**
     * Record all application access in database
     *
     * @param string $appName
     *
     * @throws Exception
     * @throws \Dcp\Db\Exception
     */
    public function recordAccesses()
    {
        $accesses = $this->getAccesses();
        foreach ($accesses as $access) {
                $access->record();
        }
    }

    /**
     * Record all context parameters in database
     *
     * @throws Exception
     */
    public function recordParameters()
    {
        $parameters = $this->getParameters();
        foreach ($parameters as $param) {
                $param->record();
        }
    }
}
