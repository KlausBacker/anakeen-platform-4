<?php

namespace Anakeen\Router;

use Anakeen\Core\ContextManager;
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
            if (!self::hasPermission($requiredAccess, $forceRecheck)) {
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
                    if (self::hasPermission($aclName, $forceRecheck)) {
                        return;
                    }
                }
                $e = new Exception("ROUTES0131", $routeInfo->name, "OR:" . implode(", ", $requiredAccess->or));
                $e->setHttpStatus(403, "Access Forbidden");
                throw $e;
            } elseif (!empty($requiredAccess->and) && count($requiredAccess->and) > 1) {
                foreach ($requiredAccess->and as $aclName) {
                    if (!self::hasPermission($aclName, $forceRecheck)) {
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

    public static function hasPermission($aclName, $forceRecheck = false)
    {
        static $first = true;
        static $acl;
        static $permission;

        if ($forceRecheck || $first === true || $permission->id_user !== ContextManager::getCurrentUser()->id) {
            $first = false;

            $acl = new \Acl();
            $permission = new \Permission();
        }
        $acl->set($aclName);

        if (!$acl->isAffected()) {
            throw new Exception("ROUTES0133", $aclName);
        }

        if ($forceRecheck || $permission->id_user !== ContextManager::getCurrentUser()->id) {
            $permission->id_user = ContextManager::getCurrentUser()->id;

            $permission->getPrivileges();
        }
        return $permission->hasPrivilege($acl->id);
    }
}
