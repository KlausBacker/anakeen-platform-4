<?php /** @noinspection PhpUnusedParameterInspection */

namespace Anakeen\Router;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Core\Settings;
use \Anakeen\Router\Config\RouterInfo;

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
    /**
     * @var RoutesConfig
     */
    protected static $routesConfig;

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
        self::cleanApacheDeflateAlterETag();

        self::$container = new \Slim\Container(self::getSlimConfig());

        self::$container['cache'] = function () {
            return new \Slim\HttpCache\CacheProvider();
        };

        self::$app = new \Slim\App(self::$container);
        // By default no cache activated
        self::$app->add(new \Slim\HttpCache\Cache('private', 0, true));


        $c = self::$app->getContainer();

        $c['phpErrorHandler'] = function ($c) {
            return new ErrorHandler();
        };

        $c['errorHandler'] = function ($c) {
            return new ErrorHandler();
        };
        $c['notFoundHandler'] = function ($c) {
            return function ($request, $response) use ($c) {
                return NotHandler::notFound($request, $c["response"]);
            };
        };
        $c['notAllowedHandler'] = function ($c) {
            return function ($request, $response, $methods) use ($c) {
                return NotHandler::notAllowed($request, $c["response"], $methods);
            };
        };
        return self::$app;
    }

    const CONFIGDIRECTORIES = "CORE_CONFIGDIRECTORIES";

    public static function getRouterConfigPaths()
    {
        $otherPathes = ContextParameterManager::getValue(Settings::NsSde, self::CONFIGDIRECTORIES);
        if ($otherPathes) {
            $path = json_decode($otherPathes);
            if ($path) {
                $configPath = $path;
            }
        }
        $configPath[] = \Anakeen\Core\Settings::RouterConfigDir;
        $absConfigPath = [];
        foreach ($configPath as $cpath) {
            $absConfigPath[] = ContextManager::getRootDirectory() . "/" . $cpath;
        }
        return $absConfigPath;
    }


    /**
     * add other path to defined routes aned parameters
     *
     * @param string $path
     *
     * @throws Exception
     * @throws \Anakeen\Exception
     */
    public static function addRouterConfigPath(string $path)
    {
        $absPath = ContextManager::getRootDirectory() . "/" . $path;
        if (!is_dir($absPath)) {
            throw new \Anakeen\Router\Exception("CORE0025", $path);
        }
        $otherPathes = ContextParameterManager::getValue(Settings::NsSde, self::CONFIGDIRECTORIES);
        $configPath = [];
        if ($otherPathes) {
            $opath = json_decode($otherPathes);
            if ($opath) {
                $configPath = $opath;
            }
        }
        $configPath[] = rtrim($path, "/");

        ContextParameterManager::setValue(Settings::NsSde, self::CONFIGDIRECTORIES, json_encode(array_unique($configPath)));
    }

    public static function deleteRouterConfigPath(string $path)
    {
        $otherPathes = ContextParameterManager::getValue(Settings::NsSde, self::CONFIGDIRECTORIES);

        $path = rtrim($path, "/");
        if ($otherPathes) {
            $opaths = json_decode($otherPathes);
            if ($opaths) {
                foreach ($opaths as $k => $opath) {
                    if ($opath === $path) {
                        unset($opaths[$k]);
                        ContextParameterManager::setValue(Settings::NsSde, self::CONFIGDIRECTORIES, json_encode(array_unique($opaths)));
                    }
                }
            }
        }
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
            /**
             * @var RouterInfo $route
             */
            if ($route->isActive() === false) {
                continue;
            }
            if (!$route->pattern) {
                continue;
            }
            if (count($route->methods) && strtoupper($route->methods[0]) === "ANY") {
                $route->methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
            }
            if (is_array($route->pattern)) {
                foreach ($route->pattern as $pattern) {
                    self::$app->map($route->methods, $pattern, $route->callable)->setName($route->name);
                }
            } else {
                self::$app->map($route->methods, $route->pattern, $route->callable)->setName($route->name);
            }
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

                if (!headers_sent()) {
                    session_cache_limiter('');
                }
                /**
                 * @var \Slim\Route $currentRoute
                 */
                $currentRoute = $request->getAttribute("route");
                $requestMethod = $request->getMethod();
                if ($currentRoute) {
                    $sParser = new \FastRoute\RouteParser\Std;

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

                                    if ($response instanceof \Psr\Http\Message\ResponseInterface === false) {
                                        throw new Exception(
                                            "ROUTES0136",
                                            $middleWare->name,
                                            $middleWare->callable
                                        );
                                    }

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


    public static function getRoutes()
    {
        return self::getRoutesConfig()->getRoutes();
    }

    public static function getMiddlewares()
    {
        return self::getRoutesConfig()->getMiddlewares();
    }

    protected static function getRoutesConfig()
    {
        if (self::$routesConfig === null) {
            self::$routesConfig = new RoutesConfig();
        }
        return self::$routesConfig;
    }

    /**
     * Workaround because apache 2.4 alter etag when deflate module is activated
     */
    protected static function cleanApacheDeflateAlterETag()
    {
        if (!empty($_SERVER["HTTP_IF_NONE_MATCH"]) && preg_match("/\-gzip/", $_SERVER["HTTP_IF_NONE_MATCH"])) {
            $_SERVER["HTTP_IF_NONE_MATCH"] = str_replace("-gzip", "", $_SERVER["HTTP_IF_NONE_MATCH"]);
        }
    }
}
