<?php

namespace Anakeen\Router;

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

    public function __construct(\stdClass $data)
    {
        $this->middlewares = $data->middlewares;
        $this->routes = $data->routes;
        static::sortRoutesByPriority($this->routes);
        $this->uniqueName($this->routes);
        static::sortMiddleByPriority($this->middlewares);
        $this->uniqueName($this->middlewares);

        static::normalizeMethods($this->routes);
        static::normalizeMethods($this->middlewares);
    }

    protected function uniqueName(array &$routes)
    {
        $uRoutes = [];
        foreach ($routes as $routeInfo) {
            $uRoutes[$routeInfo->name] = $routeInfo;
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
            if ($a->priority > $b->priority) {
                return 1;
            }
            if ($a->priority < $b->priority) {
                return -1;
            }
            if (strlen($a->pattern) > strlen($b->pattern)) {
                return 1;
            }
            if (strlen($a->pattern) < strlen($b->pattern)) {
                return -1;
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