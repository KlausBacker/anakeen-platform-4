<?php

namespace Anakeen\Routes\Middleware;

use Anakeen\Router\RouterAccess;
use Anakeen\Router\RouterLib;

class Authent
{
    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param Callable            $next
     * @param array               $args
     *
     * @return mixed
     */
    public static function authenticate(\Slim\Http\request $request, \Slim\Http\response $response, callable $next, array $args = []): \Slim\Http\response
    {
        /**
         * @var \Slim\Route $currentRoute
         */
        $currentRoute = $request->getAttribute("route");


        $configInfo = RouterLib::getRouteInfo($currentRoute->getName());
        if ($configInfo->authenticated === false) {
            return $next($request, $response);
        }

        if (!\Anakeen\Core\ContextManager::isAuthenticated()) {
            $user = \Anakeen\Core\ContextManager::authentUser();
        } else {
            $user = \Anakeen\Core\ContextManager::getCurrentUser();
        }

        \Anakeen\Core\ContextManager::initContext(
            $user,
            \Anakeen\Router\AuthenticatorManager::$session
        );

        RouterAccess::checkRouteAccess($configInfo);
        $response = $next($request, $response);

        return $response;
    }
}
