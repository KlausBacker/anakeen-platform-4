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

// @TODO Need to sort routes
$routes = $routeConfig->routes;
// @TODO Need to sort middleware
$middleWares = $routeConfig->middlewares;

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

$app->get('/foo', function ($req, $res, $args) {

    $itag="a4";
    error_log(get_class($this));
    $resWithEtag = $this->cache->withEtag($res, $itag);
    /**
     * @var \Slim\Http\response $resWithEtag
     */
    $date = date("Y-m-dTH:i:s");
  //  $resWithEtag->write($date . "aaaaaaaaaaaaaaaaaaa");
    $resWithEtag= $resWithEtag->withJSON(["date"=>$date, "idx"=> $itag]);
    var_dump((string)$resWithEtag->getBody());
    var_dump($date);




    error_log("Foo:" . $date);
    error_log("Foo:" . (string)$resWithEtag->getBody());
    return $resWithEtag;
});

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

        if ($currentRoute) {
            $sParser = new \FastRoute\RouteParser\Std;
            // print_r($currentRoute->getArguments());

            error_log($request->getMethod() . " " . $currentRoute->getPattern());
            $request=$request->withAttribute("container", $c);

            $uri = $request->getUri()->getPath();
            foreach ($middleWares as $middleWare) {
                $pattern = $middleWare->pattern;
                $patternInfos = $sParser->parse($pattern);

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

                        // @TODO : Need to match METHODS also
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
                            $response=$response->withHeader("X-Middleware", $middleWare->name);
                            $response = $callMiddleWare($request, $response, $next, $matches);

                           // error_log("After Exec" . $middleWare->name);
                            return $response;
                        });
                    }
                }
            }
        }


        $response = $next($request, $response);
        return $response;
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
