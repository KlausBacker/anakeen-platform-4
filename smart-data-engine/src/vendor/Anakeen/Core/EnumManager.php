<?php

namespace Anakeen\Core;

use Anakeen\Core\SmartStructure\DocEnum;
use Anakeen\EnumItem;
use Anakeen\Exception;

class EnumManager
{
    const _cEnum = "_CACHE_ENUM";
    const _cParent = "_CACHE_PARENT";
    const CALLABLEKEY = '::function::';
    const EXTENDABLEKEY = '.extendable';
    private static $_cache = array();

    /**
     * Return array of enumeration definition
     * the array's keys are the enum key and the values are the labels
     *
     * @param string $name           enum set reference
     * @param bool   $returnDisabled if false disabled enum are not returned
     *
     * @return array
     * @throws \Anakeen\Database\Exception
     */
    public static function getEnums($name, $returnDisabled = true)
    {
        $cached = self::_cacheFetch(self::_cEnum, $name, null, $returnDisabled);
        if ($cached !== null) {
            return $cached;
        }

        // set the enum array
        $enums = array();

        $cached = self::_cacheFetch(self::_cEnum, $name, null, $returnDisabled);
        if ($cached !== null) {
            return $cached;
        }

        $sql = sprintf("select * from docenum where name='%s' and key != '%s' order by eorder", pg_escape_string($name), pg_escape_string(self::EXTENDABLEKEY));

        DbManager::query($sql, $dbEnums);

        if (count($dbEnums) === 1 && $dbEnums[0]["parentkey"] === self::CALLABLEKEY) {
            $enums = self::getCallableEnums($dbEnums[0]["key"]);
            self::_cacheStore(self::_cEnum, $name, $enums);
            return $enums;
        }

        foreach ($dbEnums as $k => $item) {
            $dbEnums[$k]["keyPath"] = str_replace('.', '\\.', $item["key"]);
        }
        foreach ($dbEnums as $item) {
            $enumKey = $item["key"];
            $translatedEnumValue = ___($enumKey, $name);

            if ($translatedEnumValue !== $enumKey) {
                $enumLabel = $translatedEnumValue;
            } else {
                $enumLabel = $item["label"];
            }

            $enums[$enumKey] = [
                "key" => $item["key"],
                "label" => $enumLabel,
                "originalLabel" => $item["label"],
                "parentkey" => $item["parentkey"]
            ];

            if ($item["parentkey"] !== null) {
                $enums[$enumKey]["path"] = self::getCompleteEnumKey($item["keyPath"], $dbEnums);
                $enums[$enumKey]["longLabel"] = self::getCompleteEnumlabel($enumKey, $dbEnums, $name);
            } else {
                $enums[$enumKey]["path"] = $item["keyPath"];
            }
        }
        self::_cacheStore(self::_cEnum, $name, $enums);

        if (!$returnDisabled) {
            return self::_cacheFetch(self::_cEnum, $name, null, $returnDisabled);
        }
        return $enums;
    }

    /**
     * Return a boolean indicating whether the enumerate exists or not
     *
     * @param string $name enum set reference
     *
     * @return boolean
     */
    public static function existsEnums($name)
    {
        $sql = sprintf("select key from docenum where name='%s' limit 1", pg_escape_string($name));

        DbManager::query($sql, $key, true, true);

        return $key !== false;
    }

    /**
     * Return a boolean indicating whether the enumerate is extendable or not
     *
     * @param string $name enum set reference
     *
     * @return boolean
     */
    public static function isExtendable(string $enumName)
    {
        $sql = sprintf("select name from docenum where name='%s' and key = '%s' ", pg_escape_string($enumName), pg_escape_string(self::EXTENDABLEKEY));

        DbManager::query($sql, $exists, true, true);
        return $exists !== false;
    }

    protected static function getCallableEnums(string $callableString)
    {
        $parseMethod = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
        $parseMethod->parse($callableString . '()');
        $err = $parseMethod->getError();
        if ($err) {
            throw new Exception($err);
        }

        $staticClass = $parseMethod->className;
        $methodName = $parseMethod->methodName;
        if (method_exists($staticClass, $methodName)) {
            if ($methodName === "__invoke") {
                $callable = new $staticClass();
            } else {
                $callable = [$staticClass, $methodName];
            }
        } else {
            throw new Exception("ATTR1273", $callableString);
        }
        $rawItems = call_user_func($callable);
        return self::formatItems($rawItems);
    }

    /**
     * @param EnumItem[] $enumItems
     * @param EnumItem[] $parents
     *
     * @return array
     */
    private static function formatItems(array $enumItems, $parents = [])
    {
        $items = [];
        foreach ($enumItems as $item) {
            $fmtItem = [
                "key" => $item->key,
                "label" => $item->label,
                "originalLabel" => $item->label,
                "parentkey" => null
            ];
            if ($parents) {
                $path = $label = [];
                foreach ($parents as $parent) {
                    $path[] = $parent->key;
                    $label[] = $parent->label;
                }
                $path[] = $item->key;
                $label[] = $item->label;
                $fmtItem["path"] = implode(".", $path);
                $fmtItem["longLabel"] = implode("/", $label);
            }
            $items[$item->key] = $fmtItem;
            if ($item->childs) {
                $items = array_merge($items, self::formatItems($item->childs, array_merge($parents, [$item])));
            }
        }
        return $items;
    }

    private static function getCompleteEnumKey($key, array &$enums)
    {
        foreach ($enums as $item) {
            if ($item["key"] === $key) {
                if ($item["parentkey"] !== null) {
                    return sprintf("%s.%s", self::getCompleteEnumKey($item["parentkey"], $enums), $item["keyPath"]);
                } else {
                    return $item["keyPath"];
                }
            }
        }
        return '';
    }

    private static function getCompleteEnumLabel($key, array &$enums, $enumName)
    {
        foreach ($enums as $item) {
            if ($item["key"] === $key) {
                $translatedEnumValue = ___($key, $enumName);
                if ($translatedEnumValue != $key) {
                    $label = $translatedEnumValue;
                } else {
                    $label = $item["label"];
                }
                if ($item["parentkey"] !== null) {
                    return sprintf("%s/%s", self::getCompleteEnumLabel($item["parentkey"], $enums, $enumName), $label);
                } else {
                    return $label;
                }
            }
        }
        return '';
    }

    /**
     * return array of enumeration definition
     * the array'skeys are the enum single key and the values are the complete labels
     *
     * @param string $enumName       enum set reference
     * @param string $enumid         the key of enumerate (if no parameter all labels are returned
     * @param bool   $returnDisabled if false disabled enum are not returned
     *
     * @return array|string|null
     * @throws \Anakeen\Database\Exception
     */
    public static function getEnumItem($enumName, $enumid, $returnDisabled = true)
    {
        self::getEnums($enumName, $returnDisabled);

        $cached = self::_cacheFetch(self::_cEnum, $enumName, null, $returnDisabled);

        if ($cached !== null && array_key_exists($enumid, $cached)) {
            return $cached[$enumid];
        }
        return null;
    }

    /**
     * add new \item in enum list items
     *
     * @param string $enumName enum set reference
     * @param string $key      database key
     * @param string $label    human label
     *
     * @return string error message (empty means ok)
     */
    public static function addEnum($enumName, $key, $label)
    {
        $err = '';
        if ($key == "") {
            return "";
        }

        $oe = new DocEnum("", array(
            $enumName,
            $key
        ));
        self::getEnums($enumName);

        $key = str_replace(array(
            '|'
        ), array(
            '_'
        ), $key);
        $label = str_replace(array(
            '|'
        ), array(
            '_'
        ), $label);
        if (!$oe->isAffected()) {
            $oe->name = $enumName;
            $oe->key = $key;
            $oe->label = $label;
            /* Store enum in database */
            $err = $oe->add();
            if ($err == '') {
                /* Update cache */
                $cachedEnum = self::_cacheFetch(self::_cEnum, $enumName, array());
                $cachedEnum[$key] = $oe->getValues();
                self::_cacheStore(self::_cEnum, $enumName, $cachedEnum);
            }
        }

        return $err;
    }

    /**
     * reset Enum cache
     */
    public static function resetEnum()
    {
        self::_cacheFlush(self::_cEnum);
    }

    /**
     * Construct a string key
     *
     * @param mixed $k key
     *
     * @return string
     */
    private static function _cacheKey($k)
    {
        if (is_scalar($k)) {
            return $k;
        } elseif (is_array($k)) {
            return implode(':', $k);
        }
        return serialize($k);
    }

    /**
     * Check if an entry exists for the given key
     *
     * @param string $cacheId cache Id
     * @param string $k       key
     *
     * @return bool true if it exists, false if it does not exists
     */
    private static function _cacheExists($cacheId, $k)
    {
        $k = self::_cacheKey($k);
        return isset(self::$_cache[$cacheId][$k]);
    }

    /**
     * Add (or update) a key/value
     *
     * @param string          $cacheId cache Id
     * @param string|string[] $k       key
     * @param mixed           $v       value
     *
     * @return bool true on success, false on failure
     */
    private static function _cacheStore($cacheId, $k, $v)
    {
        $k = self::_cacheKey($k);
        self::$_cache[$cacheId][$k] = $v;
        return true;
    }

    /**
     * Fetch the key's value
     *
     * @param string          $cacheId        cache Id
     * @param string|string[] $enumName       key
     * @param mixed           $onCacheMiss    value returned on cache miss (default is null)
     * @param bool            $returnDisabled if false unreturn disabled enums
     *
     * @return null|mixed null on failure, mixed value on success
     */
    private static function _cacheFetch($cacheId, $enumName, $onCacheMiss = null, $returnDisabled = true)
    {
        if (self::_cacheExists($cacheId, $enumName)) {
            $ks = self::_cacheKey($enumName);
            if (!$returnDisabled) {
                $disabledKeys = DocEnum::getDisabledKeys($enumName);
                if (!empty($disabledKeys)) {
                    $cached = self::$_cache[$cacheId][$ks];
                    foreach ($disabledKeys as $dKey) {
                        unset($cached[$dKey]);
                    }
                    return $cached;
                }
            }

            return self::$_cache[$cacheId][$ks];
        }
        return $onCacheMiss;
    }

    /**
     * Flush the cache contents
     *
     * @param string|null $cacheId cache Id or null (default) to flush all caches
     *
     * @return void
     */
    private static function _cacheFlush($cacheId = null)
    {
        if ($cacheId === null) {
            self::$_cache = array();
        } else {
            self::$_cache[$cacheId] = array();
        }
    }
}
