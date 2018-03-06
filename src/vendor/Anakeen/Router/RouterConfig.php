<?php

namespace Anakeen\Router;

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
     * @var AccessInfo[]
     */
    protected $accesses;

    public function __construct(\stdClass $data)
    {
        $this->middlewares = isset($data->middlewares) ? $data->middlewares : [];
        $this->routes = isset($data->routes) ? $data->routes : [];
        $this->apps = isset($data->apps) ? $data->apps : [];
        $this->accesses = isset($data->accesses) ? $data->accesses : [];
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
                    throw new Exception("ROUTES0128", $routeInfo->name);
                }
                if ($routeInfo->override === "partial") {
                    $routeInfo->configFile=$uRoutes[$routeInfo->name]->configFile.', '.$routeInfo->configFile;
                    $uRoutes[$routeInfo->name] = (object)array_merge((array)$uRoutes[$routeInfo->name], (array)$routeInfo);
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
            if (isset($a->priority) && ! isset($b->priority)) {
                return 1;
            }
            if (!isset($a->priority) &&  isset($b->priority)) {
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

        return $this->routes;
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
        $appsInfo = [];
        foreach ($this->apps as $appData) {
            $appsInfo[$appData->name] = new AppInfo($appData);
        }

        return $appsInfo;
    }

    /**
     * @return AccessInfo[]
     */
    public function getAccesses()
    {
        $accessesInfo = [];
        foreach ($this->accesses as $appData) {
            $accessesInfo[$appData->name] = new AccessInfo($appData);
        }

        return $accessesInfo;
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
}
