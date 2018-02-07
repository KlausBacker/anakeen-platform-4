<?php

/**
 * @var Composer\Autoload\ClassLoader $loader
 */
$loader = require __DIR__ . '/../vendor/Anakeen/lib/vendor/autoload.php';
require_once __DIR__ . "/../vendor/Anakeen/WHAT/Lib.Prefix.php";
require_once __DIR__ . "/../vendor/Anakeen/WHAT/Lib.Main.php";
require_once('WHAT/autoload.php');

register_shutdown_function('handleFatalShutdown');
set_exception_handler('handleActionException');

// To add other path
// @TODO inspect config autoload path
$loader->addPsr4('Dcp\\', __DIR__ . '/../vendor/Anakeen/');

$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'debug' => true,
        "determineRouteBeforeAppMiddleware" => true,
    ]
];


$routeConfig = \Dcp\Router\RouterLib::getRouterConfig();

$routes = $routeConfig->getRoutes();
$middleWares = $routeConfig->getMiddlewares();

$container = new \Slim\Container($config);
$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};

// Add middleware to the application
$app = new \Slim\App($container);
$app->add(new \Slim\HttpCache\Cache('public', 86400));


$c = $app->getContainer();

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

// Need to reverse : Slim use the last route match
$routes = array_reverse($routes);
foreach ($routes as $route) {
    $app->map($route->methods, $route->pattern, $route->callable)->setName($route->name);
}

$app->add(
    function (\Slim\Http\request $request, \Slim\Http\response $response, $next) use ($middleWares, $c) {

        session_cache_limiter('');
        /**
         * @var \Slim\Route $currentRoute
         */
        $currentRoute = $request->getAttribute("route");
        $requestMethod=$request->getMethod();
        if ($currentRoute) {
            $sParser = new \FastRoute\RouteParser\Std;
            // print_r($currentRoute->getArguments());

            error_log($request->getMethod() . " " .$currentRoute->getName(). " ". $currentRoute->getPattern());
            $request=$request->withAttribute("container", $c);

            $uri = $request->getUri()->getPath();
            foreach ($middleWares as $middleWare) {
                $pattern = $middleWare->pattern;
                $patternInfos = $sParser->parse($pattern);


                if ($middleWare->methods !== ["ANY"] && ! in_array($requestMethod, $middleWare->methods)) {
                    continue;
                }
                $regExps = \Dcp\Router\RouterLib::parseInfoToRegExp($patternInfos);

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
                            $headerMiddleware=$response->getHeaderLine("X-Middleware");
                            $response=$response->withHeader("X-Middleware", $headerMiddleware.($headerMiddleware?", ":"").$middleWare->name);
                            $response = $callMiddleWare($request, $response, $next, $matches);

                            return $response;
                        });
                    }
                }
            }
        }

        return  $next($request, $response);
    }
);


// Define app routes
$app->get('/', function ($request, $response, $args) {
    /**
     * @var \Slim\Http\response $response
     */
    return $response->write("<h1>Welcome to Anakeen Platform 4.</h1>");
});

// Run app
$app->run();
