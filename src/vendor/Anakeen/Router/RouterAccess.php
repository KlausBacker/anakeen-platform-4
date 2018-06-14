<?php

namespace Anakeen\Router;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\Application;
use Anakeen\Router\Config\RequiredAccessInfo;
use Anakeen\Router\Config\RouterInfo;

class RouterAccess
{

    public static function checkRouteAccess(RouterInfo $routeInfo, $forceRecheck = false)
    {
        $requiredAccess = $routeInfo->requiredAccess;
        if (!$requiredAccess) {
            return;
        }

        if (is_string($requiredAccess)) {
            if (!self::hasPermission($requiredAccess, $routeInfo->applicationContext, $forceRecheck)) {
                $e = new Exception("ROUTES0131", $routeInfo->name, $requiredAccess);
                $e->setHttpStatus(403, "Access Forbidden");
                throw $e;
            }
        } else {
            /**
             * @var RequiredAccessInfo $requiredAccess
             */
            if (!empty($requiredAccess->or) && count($requiredAccess->or) > 1) {
                foreach ($requiredAccess->or as $aclName) {
                    if (self::hasPermission($aclName, $routeInfo->applicationContext, $forceRecheck)) {
                        return;
                    }
                }
                $e = new Exception("ROUTES0131", $routeInfo->name, "OR:" . implode(", ", $requiredAccess->or));
                $e->setHttpStatus(403, "Access Forbidden");
                throw $e;
            } elseif (!empty($requiredAccess->and) && count($requiredAccess->and) > 1) {
                foreach ($requiredAccess->and as $aclName) {
                    if (!self::hasPermission($aclName, $routeInfo->applicationContext, $forceRecheck)) {
                        $e = new Exception("ROUTES0131", $routeInfo->name, "AND:" . implode(", ", $requiredAccess->and));
                        $e->setHttpStatus(403, "Access Forbidden");
                        throw $e;
                    }
                }
            } else {
                throw new Exception("ROUTES0132");
            }
        }
    }

    public static function hasPermission($aclName, $appName = "CORE", $forceRecheck = false)
    {
        static $first = true;
        static $acl;
        static $permission;
        static $app;

        if ($forceRecheck || $first === true || $permission->id_user !== ContextManager::getCurrentUser()->id || $app->name !== $appName) {
            $first = false;
            $app = ContextManager::getCurrentApplication();
            if ($app !== $appName) {
                $app = new Application();
                $app->set($appName);
            }
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

        if ($forceRecheck || $permission->id_application !== $acl->id_application
            || $permission->id_application !== $app->id
            || $permission->id_user !== ContextManager::getCurrentUser()->id) {
            $permission->id_application = $acl->id_application;
            $permission->id_user = ContextManager::getCurrentUser()->id;

            $permission->getPrivileges();
        }
        return $permission->hasPrivilege($acl->id);
    }
}
