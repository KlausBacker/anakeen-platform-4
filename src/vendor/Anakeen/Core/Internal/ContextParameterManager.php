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


    public static function setValue($ns, $name, $val, $type = Param::PARAM_GLB)
    {
        $key = $ns . "::" . $name;
        $p = new Param("", [$key, $type]);

        if (!$p->isAffected() && $type !== Param::PARAM_GLB) {
            $p->val = $val;
            $p->type = $type;
            $p->name = $key;
            $p->add();
        } elseif ($p->isAffected()) {
            $p->val = $val;
            $err = $p->modify();
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
     * @param string $val       new value
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

        $u = ContextManager::getCurrentUser();
        if ($u) {
            if (!self::$cache) {
                self::initCache();
            }
            if (isset(self::$cache[$key])) {
                return self::$cache[$key];
            }
        } else {
            return self::getDbValue($key, $def);
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
