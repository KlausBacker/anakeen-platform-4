<?php

namespace Anakeen\Script;

use Anakeen\Core\FileMime;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class RouteCommand
{
    /**
     * @var \Anakeen\Router\RoutesConfig
     */
    protected $routesConfig;

    public function __construct()
    {
        $this->routesConfig = new \Anakeen\Router\RoutesConfig();
    }

    /**
     * @return \Anakeen\Router\RouterInfo[]
     */
    public function getRouteList()
    {

        $routes = $this->routesConfig->getRoutes();
        return ($routes);
    }

    /**
     * @param string $routeId
     * @param array  $args
     *
     * @return string
     * @throws \Anakeen\Router\Exception
     * @throws \Dcp\Exception
     */
    public function requestRoute($routeId, $args)
    {
        $routes = $this->routesConfig->getRoutes();
        if (!isset($routes[$routeId])) {
            throw new \Anakeen\Router\Exception(sprintf("Route \"%s\" not exists", $routeId));
        }
        $route = $routes[$routeId];
        if (!empty($args["method"])) {
            $method = $args["method"];
        } else {
            $method = "GET";
        }
        if (is_array($route->pattern)) {
            $uri = current($route->pattern);
        } else {
            $uri = $route->pattern;
        }
        $patternArgs = [];

        foreach ($args as $k => $arg) {
            if ($k === "route" || $k === "query" || $k === "method" || $k === "content") {
                continue;
            }
            if (substr($k, 0, 4) === "arg-") {
                $patternArgs[substr($k, 4)] = $arg;
            } else {
                $patternArgs[$k] = $arg;
            }
        }
        if (preg_match_all("/\{([^\}:]*)[\}:]/", $uri, $regs)) {
            $needArgs = $regs[1];
        } else {
            $needArgs = [];
        }
        foreach ($needArgs as $argId) {
            if (!isset($patternArgs[$argId])) {
                throw new \Anakeen\Router\Exception(sprintf("Needed route arg \"%s\" not set", $argId));
            }
        }


        // Replace pattern args identified by {}
        foreach ($args as $k => $arg) {
            $uri = preg_replace('/{' . $k . '[^}]*}/', $arg, $uri);
        }
        $uri = preg_replace('/\[[^\]]*\]/', "", $uri);

        $envConfig = [
            'REQUEST_METHOD' => $method,
            'HTTP_ACCEPT' => 'application/json, *',
            'REQUEST_URI' => $uri
        ];
        if (!empty($args["query"])) {
            if ($args["query"][0] === "?") {
                $args["query"] = substr($args["query"], 1);
            }
            $envConfig['QUERY_STRING'] = $args["query"];
        }
        if (!empty($args["content"])) {
            if (!file_exists($args["content"])) {
                throw new  \Anakeen\Router\Exception(sprintf("Content file \"%s\" not found", $args["content"]));
            }
            $mime = FileMime::getMimeFile($args["content"]);
            $envConfig['CONTENT_TYPE'] = $mime;
            $envConfig['CONTENT'] = file_get_contents($args["content"]);
        }


        $env = Environment::mock($envConfig);

        $request = Request::createFromEnvironment($env);

        $container = new Container();
        $container['cache'] = function () {
            return new \Slim\HttpCache\CacheProvider();
        };

        $request = $request->withAttribute("container", $container);
        $response = new Response();
        $callable = $route->callable;
        if (class_exists($callable)) {
            $callable = [new $callable($container), "__invoke"];
        }

        if (is_callable($callable)) {
            ShellManager::initContext($route->applicationContext ? $route->applicationContext : "CORE");
            /**
             * @var Response $routeResponse
             */
            $routeResponse = call_user_func_array($callable, [$request, $response, $patternArgs]);
        } else {
            throw new \Anakeen\Router\Exception(sprintf("Cannot call \"%s\"", print_r($callable, true)));
        }
        return (string)$routeResponse->getBody();
    }
}
