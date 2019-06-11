<?php

namespace Anakeen\Routes\Tests;

/**
 * Fake
 */
class TestRouteConfig
{
    /**
     *
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response $response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $routeConfig = \Anakeen\Router\RouterLib::getRouterConfig();
        return $response->withJson(
            [
                "routes" => $routeConfig->getFileConfigRoutes(),
                "middleware" => $routeConfig->getFileConfigMiddlewares(),
                "accesses" => $routeConfig->getAccesses()
            ]
        );
    }
}
