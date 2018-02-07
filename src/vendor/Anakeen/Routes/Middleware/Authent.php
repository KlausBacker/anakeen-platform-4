<?php

namespace Dcp\Routes\Middleware;

use Dcp\Router\RouterLib;

class Authent
{
    /**
     * @param       \Slim\Http\request  $request
     * @param       \Slim\Http\response $response
     * @param       Callable            $next
     * @param array                     $args
     *
     * @return mixed
     */
    public static function Authenticate($request, $response, $next, $args = [])
    {
        error_log("Before inside" . __METHOD__);

        /**
         * @var \Slim\Route $currentRoute
         */
        $currentRoute = $request->getAttribute("route");


        $configInfo= RouterLib::getRouteInfo($currentRoute->getName());
        if ($configInfo->authenticated === false) {
            return $next($request, $response);
        }

        $user=\Dcp\Core\ContextManager::authentUser();
        \Dcp\Core\ContextManager::initContext($user, "CORE", "", \AuthenticatorManager::$session);

       // error_log("Exec Middle" . __METHOD__ . " : " . print_r($args, true));
        $response = $next($request, $response);
       // error_log("After inside" . __METHOD__);
        return $response;
    }
}
