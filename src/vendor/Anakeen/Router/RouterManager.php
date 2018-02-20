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
     * Get main router
     * Is configured with error handlers and default cache
     *
     * @return \Slim\App
     */
    public static function getSlimApp()
    {

        self::$container = new \Slim\Container(self::getSlimConfig());

        self::$container['cache'] = function () {
            return new \Slim\HttpCache\CacheProvider();
        };

        self::$app = new \Slim\App(self::$container);
        // By default no cache activated
        self::$app->add(new \Slim\HttpCache\Cache('private', 0, true));


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

    /**
     * Add all availables routes to main router
     *
     * @param RouterInfo[] $routes
     */
    public static function addRoutes(array $routes)
    {
        // Need to reverse : Slim use the last route match
        $routes = array_reverse($routes);
        foreach ($routes as $route) {
            if (count($route->methods) && strtoupper($route->methods[0]) === "ANY") {
                $route->methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
            }
            self::$app->map($route->methods, $route->pattern, $route->callable)->setName($route->name);
        }
    }

    /**
     * Add matches middleWares to main router
     *
     * @param RouterInfo[] $middleWares list of all available middlewares
     */
    public static function addMiddlewares(array $middleWares)
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

                    // @TODO to delete : used to debug for the moment
                    error_log($request->getMethod() . " " . $currentRoute->getName() . " "
                        . $currentRoute->getPattern());
                    $request = $request->withAttribute("container", $c);

                    $uri = $request->getUri()->getPath();
                    foreach ($middleWares as $middleWare) {
                        /**
                         * @var RouterInfo $middleWare
                         */
                        $pattern = $middleWare->pattern;
                        $patternInfos = $sParser->parse($pattern);

                        // Reject if HTTP method not match
                        if ($middleWare->methods !== ["ANY"] && !in_array($requestMethod, $middleWare->methods)) {
                            continue;
                        }
                        $regExps = \Anakeen\Router\RouterLib::parseInfoToRegExp($patternInfos);

                        // Add all middleware matches
                        foreach ($regExps as $regExp) {
                            if (preg_match($regExp, $uri, $matches)) {
                                foreach ($matches as $k => $v) {
                                    if (is_numeric($k)) {
                                        unset($matches[$k]);
                                    }
                                }

                                $currentRoute->add(function ($request, $response, $next) use ($middleWare, $matches) {

                                    $callMiddleWare = $middleWare->callable;

                                    if (!is_callable($callMiddleWare)) {
                                        if (!class_exists($callMiddleWare)) {
                                            throw new \Anakeen\Router\Exception(
                                                sprintf(
                                                    "Middleware \"%s\" : Class \"%s\" not exists",
                                                    $middleWare->name,
                                                    $middleWare->callable
                                                )
                                            );
                                        } else {
                                            $callMiddleWare = new $callMiddleWare;
                                            if (!is_callable($callMiddleWare)) {
                                                throw new \Anakeen\Router\Exception(
                                                    sprintf(
                                                        "Middleware \"%s\" : not Callable \"%s\"",
                                                        $middleWare->name,
                                                        $middleWare->callable
                                                    )
                                                );
                                            }
                                        }
                                    }
                                    /**
                                     * @var \Slim\Http\Response $response
                                     */
                                    $headerMiddleware = $response->getHeaderLine("X-Middleware");
                                    // Add middleware used in header
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
