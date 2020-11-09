<?php

namespace Anakeen\Router;

use Anakeen\Core\ContextManager;
use Anakeen\Router\Config\RequiredAccessInfo;
use Anakeen\Router\Config\RouterInfo;

class RouterAccess
{
    /** @var \Permission */
    protected static $perm;
    /** @var \Acl */
    protected static $acl;

    public static function checkRouteAccess(RouterInfo $routeInfo, $forceRecheck = false)
    {
        $requiredAccess = $routeInfo->requiredAccess;
        if (!$requiredAccess) {
            return;
        }

        /**
         * @var RequiredAccessInfo $requiredAccess
         */
        if (!empty($requiredAccess->or) && count($requiredAccess->or) > 0) {
            foreach ($requiredAccess->or as $aclName) {
                if (self::hasPermission($aclName, $forceRecheck)) {
                    return;
                }
            }
            $e = new Exception("ROUTES0131", $routeInfo->name, "OR:" . implode(", ", $requiredAccess->or));
            $e->setHttpStatus(403, "Access Forbidden");
            throw $e;
        } elseif (!empty($requiredAccess->and) && count($requiredAccess->and) > 0) {
            foreach ($requiredAccess->and as $aclName) {
                if (!self::hasPermission($aclName, $forceRecheck)) {
                    $e = new Exception("ROUTES0131", $routeInfo->name, "AND: " . implode(", ", $requiredAccess->and));
                    $e->setHttpStatus(403, "Access Forbidden");
                    throw $e;
                }
            }
        } else {
            throw new Exception("ROUTES0132");
        }
    }

    public static function hasPermission($aclName, $forceRecheck = false)
    {
        if (!self::$acl) {
            self::$acl = new \Acl();
        }

        self::$acl->set($aclName);

        if (!self::$acl->isAffected()) {
            throw new Exception("ROUTES0133", $aclName);
        }

        if ($forceRecheck || !self::$perm) {
            self::$perm = new \Permission();
        }

        if ($forceRecheck || self::$perm->id_user !== ContextManager::getCurrentUser()->id) {
            self::$perm->id_user = ContextManager::getCurrentUser()->id;
        }
        return self::$perm->hasPrivilege(self::$acl->id);
    }
}
