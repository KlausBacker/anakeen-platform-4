<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Dcp\Exception;

/**
 * Manage context parameters
 * Set and get context parameters
 *
 * @class ContextParameterManager
 *
 */
class ContextParameterManager
{
    protected static $cacheUser;
    /**
     * @var array
     * @private
     */
    private static $cache = array();
    private static $volatile = array();

    /**
     * for internal purpose only
     *
     * @private
     */
    public static function resetCache()
    {
        self::$cache = array();
    }


    public static function setValue($ns, $name, $val, $type = Param::PARAM_GLB)
    {
        $key = $ns . "::" . $name;
        $p = new Param("", [$key, $type]);

        if (!$p->isAffected() && $type[0] === Param::PARAM_USER) {
            if ($val !== null) {
                $p->val = $val;
                $p->type = $type;
                $p->name = $key;
                $p->add();
            }
        } elseif ($p->isAffected()) {
            if ($type[0] === Param::PARAM_USER && $val === null) {
                $err = $p->delete();
            } else {
                $p->val = $val;
                $err = $p->modify();
            }
            if ($err) {
                throw new Exception(sprintf("Cannot modify context parameter %s : %s", $key, $err));
            }
            self::$cache[$key] = $val;
        } else {
            throw new Exception(sprintf("Unknow context parameter %s", $key));
        }
    }

    /**
     * Set value to a user parameter
     * @param string $ns        parameter namespace
     * @param string $name      parameter name
     * @param string $val       new value (if null the user value will be deleted)
     * @param int    $accountId (user system id)  - 0 means current user id
     * @throws Exception
     */
    public static function setUserValue(string $ns, string $name, $val, int $accountId = 0)
    {
        if ($accountId === 0) {
            $accountId = ContextManager::getCurrentUser()->id;
        }
        self::setValue($ns, $name, $val, sprintf("%s%d", Param::PARAM_USER, $accountId));
    }

    public static function getValue(string $ns, string $name, $def = null)
    {
        $key=$ns.'::'.$name;
        if (isset(self::$volatile[$key])) {
            return self::$volatile[$key];
        }

        if (!self::$cache) {
            self::initCache();
        }
        $u = ContextManager::getCurrentUser();
        if ($u) {
            if (!self::$cacheUser) {
                self::initCacheUser();
            }
        }
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        return $def;
    }

    public static function setVolatile($ns, $name, $val)
    {
        $key=$ns.'::'.$name;
        if ($val !== null) {
            self::$volatile[$key] = $val;
        } else {
            unset(self::$volatile[$key]);
        }
    }
    
    protected static function initCache()
    {
        $sql = sprintf("select  paramv.* from paramv where type = 'G'");
        DbManager::query($sql, $params);
        foreach ($params as $param) {
            self::$cache[$param["name"]] = $param["val"];
        }
    }
    protected static function initCacheUser()
    {
        $sql = sprintf("select  paramv.* from paramv where type = 'U%d'", ContextManager::getCurrentUser()->id);
        DbManager::query($sql, $params);
        foreach ($params as $param) {
            self::$cache[$param["name"]] = $param["val"];
        }
        self::$cacheUser=true;
    }
}
