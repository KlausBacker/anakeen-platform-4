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


    public static function setValue($name, $val, $type = Param::PARAM_GLB)
    {
        $p = new Param("", [$name, $type]);
        if ($p->isAffected()) {
            $p->val = $val;
            $err = $p->modify();
            if ($err) {
                throw new Exception(sprintf("Cannot modify context parameter %s : %s", $name, $err));
            }
        } else {
            throw new Exception(sprintf("Unknow context parameter %s", $name));
        }
    }

    public static function setUserValue($name, $val, int $accountId = 0)
    {
        if ($accountId === 0) {
            $accountId=ContextManager::getCurrentUser()->id;
        }
        self::setValue($name, $val, sprintf("%s%d", Param::PARAM_USER, $accountId));
    }

    public static function getValue($name, $def = null)
    {
        if (isset(self::$volatile[$name])) {
            return self::$volatile[$name];
        }

        $u = ContextManager::getCurrentUser();
        if ($u) {
            if (!self::$cache) {
                self::initCache();
            }
            if (isset(self::$cache[$name])) {
                return self::$cache[$name];
            }
        } else {
            return self::getDbValue($name, $def);
        }
        return $def;
    }

    public static function setVolatile($name, $val)
    {
        if ($val !== null) {
            self::$volatile[$name] = $val;
        } else {
            unset(self::$volatile[$name]);
        }
    }

    protected static function getDbValue($name, $def)
    {
        $sql = sprintf("select  paramv.val from paramv where name='%s' and type='G' order by name, type", pg_escape_string($name));
        DbManager::query($sql, $value, true, true);

        return ($value !== false) ? $value : $def;
    }

    protected static function initCache()
    {
        $sql = sprintf("select  paramv.* from paramv where type = 'G' or type = 'U%d' order by name, type", ContextManager::getCurrentUser()->id);
        DbManager::query($sql, $params);
        foreach ($params as $param) {
            self::$cache[$param["name"]] = $param["val"];
        }
    }
}
