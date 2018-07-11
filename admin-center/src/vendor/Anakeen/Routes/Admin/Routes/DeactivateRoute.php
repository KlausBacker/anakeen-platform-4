<?php

namespace Anakeen\Routes\Admin\Routes;

use Anakeen\Router\ApiV2Response;
use Dcp\Core\Exception;

class DeactivateRoute
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     * @throws \Dcp\Core\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $route = \Anakeen\Router\RouterLib::getRouteInfo($args['routeName']);
        try {
            $route->setActive(false);
        }catch (Exception $e) {
            return ApiV2Response::withData($response, $e)->write($e);
        }
        return ApiV2Response::withData($response,$route);
    }
}
