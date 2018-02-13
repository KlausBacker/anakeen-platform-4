<?php

namespace Anakeen\Router;

class RouterManager
{
    /**
     * @var \Slim\Container $container
     */
    protected static $container;
    /**
     * @var \Slim\App $app
     */
    protected static $app;

    public static function getSlimConfig()
    {
        $config = [
            'settings' => [
                'displayErrorDetails' => true,
                'debug' => true,
                "determineRouteBeforeAppMiddleware" => true,
            ]
        ];
        return $config;
    }


    /**
     * @return \Slim\App
     */
    public static function getSlimApp()
    {

        self::$container = new \Slim\Container(self::getSlimConfig());
        self::$container['cache'] = function () {
            return new \Slim\HttpCache\CacheProvider();
        };

        self::$app = new \Slim\App(self::$container);
        self::$app->add(new \Slim\HttpCache\Cache('private', 86400));


        $c = self::$app->getContainer();

        $c['phpErrorHandler'] = function ($c) {
            return new \Dcp\Router\ErrorHandler();
        };

        $c['errorHandler'] = function ($c) {
            return new \Dcp\Router\ErrorHandler();
        };
        $c['notFoundHandler'] = function ($c) {
            return function ($request, $response) use ($c) {
                return \Dcp\Router\NotHandler::notFound($request, $c["response"]);
            };
        };
        $c['notAllowedHandler'] = function ($c) {
            return function ($request, $response, $methods) use ($c) {
                return \Dcp\Router\NotHandler::notAllowed($request, $c["response"], $methods);
            };
        };
        return self::$app;
    }

    public static function addRoutes($routes)
    {
        // Need to reverse : Slim use the last route match
        $routes = array_reverse($routes);
        foreach ($routes as $route) {
            self::$app->map($route->methods, $route->pattern, $route->callable)->setName($route->name);
        }
    }

    public static function addMiddlewares($middleWares)
    {
        $c = self::$container;
        self::$app->add(
            function (\Slim\Http\request $request, \Slim\Http\response $response, $next) use ($middleWares, $c) {

                session_cache_limiter('');
                /**
                 * @var \Slim\Route $currentRoute
                 */
                $currentRoute = $request->getAttribute("route");
                $requestMethod = $request->getMethod();
                if ($currentRoute) {
                    $sParser = new \FastRoute\RouteParser\Std;
                    // print_r($currentRoute->getArguments());

                    error_log($request->getMethod() . " " . $currentRoute->getName() . " "
                        . $currentRoute->getPattern());
                    $request = $request->withAttribute("container", $c);

                    $uri = $request->getUri()->getPath();
                    foreach ($middleWares as $middleWare) {
                        $pattern = $middleWare->pattern;
                        $patternInfos = $sParser->parse($pattern);


                        if ($middleWare->methods !== ["ANY"] && !in_array($requestMethod, $middleWare->methods)) {
                            continue;
                        }
                        $regExps = \Anakeen\Router\RouterLib::parseInfoToRegExp($patternInfos);

                        // Add all middleware matches
                        foreach ($regExps as $regExp) {
                            if (preg_match($regExp, $uri, $matches)) {
                                // error_log("Add Middleware : " . $middleWare->name);

                                foreach ($matches as $k => $v) {
                                    if (is_numeric($k)) {
                                        unset($matches[$k]);
                                    }
                                }

                                $currentRoute->add(function ($request, $response, $next) use ($middleWare, $matches) {
                                    // error_log("Before Exec " . $middleWare->name);
                                    $callMiddleWare = $middleWare->callable;


                                    if (!is_callable($callMiddleWare)) {
                                        throw new \Dcp\Exception(
                                            sprintf(
                                                "Middleware \"%s\" not callable : \"%s\"",
                                                $middleWare->name,
                                                $middleWare->callable
                                            )
                                        );
                                    }
                                    /**
                                     * @var \Slim\Http\Response $response
                                     */
                                    $headerMiddleware = $response->getHeaderLine("X-Middleware");
                                    $response = $response->withHeader(
                                        "X-Middleware",
                                        $headerMiddleware . ($headerMiddleware ? ", " : "") . $middleWare->name
                                    );
                                    $response = $callMiddleWare($request, $response, $next, $matches);

                                    return $response;
                                });
                            }
                        }
                    }
                }

                return $next($request, $response);
            }
        );
    }
}
