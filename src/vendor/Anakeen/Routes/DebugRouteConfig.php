<?php

namespace Anakeen\Routes;

/**
 * Fake
 */
class DebugRouteConfig
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
