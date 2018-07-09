<?php

namespace My;

/**
 * Fake
 */
class MyRouteConfig
{
    public function __invoke(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        array $args
    ) {
        $routeConfig = \Anakeen\Router\RouterLib::getRouterConfig();

        return $response->withJson(
            [
                "routes" => $routeConfig->getFileConfigRoutes(),
                "middleware" => $routeConfig->getFileConfigMiddlewares()
            ]

        );
    }
}
