<?php

namespace Anakeen\Routes\Middleware;

use Anakeen\Router\Exception;
use Anakeen\Router\RequiredAccessInfo;
use Anakeen\Router\RouterLib;
use Anakeen\Core\ContextManager;

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
        /**
         * @var \Slim\Route $currentRoute
         */
        $currentRoute = $request->getAttribute("route");


        $configInfo = RouterLib::getRouteInfo($currentRoute->getName());
        if ($configInfo->authenticated === false) {
            return $next($request, $response);
        }

        $user = \Anakeen\Core\ContextManager::getCurrentUser();
        if (!$user) {
            $user = \Anakeen\Core\ContextManager::authentUser();
        }

        if (!$configInfo->applicationContext) {
            $configInfo->applicationContext = "CORE";
        }
        \Anakeen\Core\ContextManager::initContext(
            $user,
            $configInfo->applicationContext,
            "",
            \AuthenticatorManager::$session
        );

        self::checkRouteAccess($configInfo->requiredAccess);

        $response = $next($request, $response);
        return $response;
    }

    protected static function checkRouteAccess($access)
    {
        if (!$access) {
            return;
        }

        if (is_string($access)) {
            if (!self::hasPermission($access)) {
                $e = new Exception("ROUTES0131", $access);
                $e->setHttpStatus(403, "Access Forbidden");
                throw $e;
            }
        } else {
            /**
             * @var RequiredAccessInfo $access
             */
            if (!empty($access->or) && count($access->or) > 1) {
                foreach ($access->or as $aclName) {
                    if (self::hasPermission($aclName)) {
                        return;
                    }
                }
                $e = new Exception("ROUTES0131", "OR:" . implode(", ", $access->or));
                $e->setHttpStatus(403, "Access Forbidden");
                throw $e;
            } elseif (!empty($access->and) && count($access->and) > 1) {
                foreach ($access->and as $aclName) {
                    if (!self::hasPermission($aclName)) {
                        $e = new Exception("ROUTES0131", "AND:" . implode(", ", $access->and));
                        $e->setHttpStatus(403, "Access Forbidden");
                        throw $e;
                    }
                }
            } else {
                throw new Exception("ROUTES0132");
            }
        }
    }

    protected static function hasPermission($aclName)
    {
        static $first = true;
        static $acl;
        static $permission;
        static $app;

        if ($first === true) {
            $first = false;
            $app = ContextManager::getCurrentApplication();
            $acl = new \Acl();
            $permission = new \Permission();
            $permission->id_user = ContextManager::getCurrentUser()->id;
        }

        if (!$acl->set($aclName, $app->id) && $app->parent && $app->id !== $app->parent->id) {
            $acl->set($aclName, $app->parent->id);
        };
        if (!$acl->isAffected()) {
            throw new Exception("ROUTES0133", $aclName);
        }

        if ($permission->id_application !== $acl->id_application
            || $permission->id_user !== ContextManager::getCurrentUser()->id) {
            $permission->id_application = $acl->id_application;
            $permission->id_user = ContextManager::getCurrentUser()->id;


            $permission->getPrivileges();
        }

        return $permission->hasPrivilege($acl->id);
    }
}
