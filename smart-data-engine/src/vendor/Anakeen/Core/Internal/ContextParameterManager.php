<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Exception;

/**
 * Manage context parameters
 * Set and get context parameters
 *
 */
class ContextParameterManager
{
    protected static $cacheUser =false;
    protected static $cacheDef;
    /**
     * @var array
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
        self::$cache = [];
        self::$cacheUser =false;
        self::$cacheDef =[];
    }


    /**
     * Set value to a global parameter
     * @param string $ns parameter namespace
     * @param string $name parameter name
     * @param string $val new value (if null the user value will be deleted)
     * @param string $type parameter type
     * @throws Exception
     */
    public static function setValue($ns, $name, $val, $type = Param::PARAM_GLB)
    {
        $key = $ns . "::" . $name;
        $p = new Param("", [$key, $type]);

        if (!$p->isAffected() && $type[0] === Param::PARAM_USER) {
            if (!self::exists($ns, $name, true)) {
                throw new Exception("CORE0103", $key);
            }
            if ($val !== null) {
                $p->val = $val;
                $p->type = $type;
                $p->name = $key;
                $p->add();
            }
        } elseif ($p->isAffected()) {
            if ($type[0] === Param::PARAM_USER && $val === null) {
                $err = $p->delete();
                self::resetCache();
            } else {
                $p->val = $val;
                $err = $p->modify();
                self::$cache[$key] = $val;
            }
            if ($err) {
                throw new Exception("CORE0101", $key, $err);
            }
        } else {
            throw new Exception("CORE0102", $key);
        }
    }

    /**
     * Set value to a user parameter
     * @param string $ns parameter namespace
     * @param string $name parameter name
     * @param string $val new value (if null the user value will be deleted)
     * @param int $accountId (user system id)  - 0 means current user id
     * @throws Exception
     */
    public static function setUserValue(string $ns, string $name, $val, int $accountId = 0)
    {
        if ($accountId === 0) {
            $accountId = ContextManager::getCurrentUser()->id;
        }
        self::setValue($ns, $name, $val, sprintf("%s%d", Param::PARAM_USER, $accountId));
    }

    /**
     * Get value for an user parameter
     * If the user has not a specific value, return the common value
     * If the common value is not found return $def
     * @param string $ns parameter namespace
     * @param string $name parameter name
     * @param int $accountId (user system id)  - 0 means current user id
     * @param mixed $def the return value if not found
     * @return string the value or $def if not found
     * @throws Exception if parameter is not defined
     */
    public static function getUserValue(string $ns, string $name, int $accountId, $def = null)
    {
        $key = $ns . '::' . $name;
        $sqlRequest = sprintf(
            "select paramv.val from paramdef, paramv where paramdef.name = paramv.name and paramv.type='%s' and paramdef.name='%s';",
            pg_escape_string("U" . $accountId),
            pg_escape_string($key)
        );

        DbManager::query($sqlRequest, $output, true, true);
        if ($output === false) {
            if (!self::$cache) {
                self::initCache();
            }

            if (array_key_exists($key, self::$cache)) {
                if (! self::exists($ns, $name, true)) {
                    throw new Exception("CORE0104", $key);
                }
                return self::$cache[$key] ?: $def;
            }
            throw new Exception("CORE0100", $key);
        }

        return $output;
    }


    /**
     * get namespace if parameter name if found and unique
     * @param string $name
     * @return string the namespace (empty if not)
     */
    public static function getNs(string $name)
    {
        $sql = sprintf("select name from paramdef where name ~ '::%s$'", pg_escape_string($name));
        DbManager::query($sql, $results, true);
        if (count($results) === 1) {
            list($ns) = explode("::", $results[0]);
            return $ns;
        }
        return "";
    }

    /**
     * Get value for a global parameter
     * If the user has a specific value, return the user value
     * If the common value is empty or not found return $def
     * @param string $ns parameter namespace
     * @param string $name parameter name
     * @param mixed $def the return value if not found
     * @return mixed|null
     */
    public static function getValue(string $ns, string $name, $def = null)
    {
        $key = $ns . '::' . $name;
        if (isset(self::$volatile[$key])) {
            return self::$volatile[$key];
        }

        if (!self::$cache) {
            self::initCache();
        }
        if (ContextManager::isAuthenticated()) {
            if (!self::$cacheUser) {
                self::initCacheUser();
            }
        }
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        return $def;
    }


    /**
     * Verify if parameter is defined
     * @param string $ns parameter namespace
     * @param string $name parameter name
     * @param bool $isForUser set to true to verify it is a user param definition
     * @return bool
     */
    public static function exists($ns, $name, $isForUser = false)
    {
        $key = $ns . '::' . $name;
        if (!self::$cacheDef) {
            $sql = sprintf("select  name, isuser from paramdef");
            DbManager::query($sql, $params);
            foreach ($params as $param) {
                self::$cacheDef[$param["name"]] = $param["isuser"] === "Y";
            }
        }
        return array_key_exists($key, self::$cacheDef) && (!$isForUser || self::$cacheDef[$key]===true);
    }

    /**
     * Add parameter value for current request
     * This parameter no need to be declared
     * It is use by getValue first
     * Could be use to change value or create new parameter during the request
     * @param string $ns parameter namespace
     * @param string $name parameter name
     * @return void
     */
    public static function setVolatile($ns, $name, $val)
    {
        $key = $ns . '::' . $name;
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
        self::$cacheUser = true;
    }
}
