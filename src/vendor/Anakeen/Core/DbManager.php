<?php

namespace Anakeen\Core;

class DbManager
{
    protected static $inTransition = false;
    protected static $savepoint;
    protected static $masterLock=false;
    protected static $lockpoint;
    protected static $pgConnection=null;
    protected static $dbRessource;

    public static function getDbAccess()
    {
        if (static::$pgConnection) {
            return static::$pgConnection;
        }

        $configFile = ContextManager::getRootDirectory() . "/" . Settings::DbAccessFilePath;
        if (!file_exists($configFile)) {
            throw new \Dcp\Core\Exception("CORE0015", $configFile);
        }
        $pgservice_core = null;
        if (!include($configFile)) {
            throw new \Dcp\Core\Exception("CORE0016", $configFile);
        }

        if (!$pgservice_core) {
            throw new \Dcp\Core\Exception("CORE0016", $configFile);
        }
        static::$pgConnection = sprintf("service='%s'", $pgservice_core);

        return static::$pgConnection;
    }


    /**
     * @return null|resource
     * @throws \Anakeen\Database\Exception
     */
    public static function getDbid()
    {
        if (static::$dbRessource) {
            return static::$dbRessource;
        }
        static::$dbRessource = pg_connect(static::getDbAccess());
        if (!static::$dbRessource) {
            // fatal error
            header('HTTP/1.0 503 DB connection unavalaible');
            throw new \Anakeen\Database\Exception('DB0101', static::getDbAccess());
        }

        return static::$dbRessource;
    }

    /**
     * send simple query to database
     *
     * @param string            $query        sql query
     * @param string|bool|array &$result      query result
     * @param bool              $singlecolumn set to true if only one field is return
     * @param bool              $singleresult set to true is only one row is expected (return the first row). If is combined with singlecolumn return the value not an array, if no
     *                                        results and $singlecolumn is true then $results is false
     *
     * @throws \Anakeen\Database\Exception
     * @return void
     */
    public static function query($query, &$result = array(), $singlecolumn = false, $singleresult = false)
    {
        static $sqlStrict = null;

        $dbid = static::getDbid();

        // error_log("SQL>".$query."\n".Debug::getDebugStackString(2, 5));

        $result = array();
        $r = @pg_query($dbid, $query);
        if ($r) {
            if (pg_numrows($r) > 0) {
                if ($singlecolumn) {
                    $result = pg_fetch_all_columns($r, 0);
                } else {
                    $result = pg_fetch_all($r);
                }
                if ($singleresult) {
                    $result = $result[0];
                }
            } else {
                if ($singleresult && $singlecolumn) {
                    $result = false;
                }
            }
        } else {
            throw new \Anakeen\Database\Exception('DB0100', pg_last_error($dbid), $query);
        }
    }

    /**
     * set a database transaction save point
     *
     * @param string $point point identifier
     *
     * @return void
     */
    public static function savePoint($point)
    {
        $idbid = intval(static::getDbid());

        if (empty(static::$savepoint[$idbid])) {
            static::$savepoint[$idbid] = array(
                $point
            );
            static::query("begin");
            static::$inTransition=true;
        } else {
            static::$savepoint[$idbid][] = $point;
        }

        static::query(sprintf('savepoint "%s"', pg_escape_string($point)));
    }

    public static function inTransition()
    {
        return static::$inTransition;
    }

    /**
     * Set a database transaction advisory lock
     *
     * - A transaction advisory lock can only be used within an existing
     *   transaction.  So, a transaction must have been explicitly opened
     *   by a call to  \Anakeen\Database\Exception\Object::savePoint() before using  \Anakeen\Database\Exception\Object::lockPoint().
     * - The lock is automatically released when the transaction is
     *   commited or rolled back.
     *
     * @param int    $exclusiveLock       Lock's identifier as a signed integer in the int32 range
     *                                    (i.e. in the range [-2147483648, 2147483647]).
     * @param string $exclusiveLockPrefix Lock's prefix string limited up to 4 bytes.
     *
     * @throws \Anakeen\Database\Exception
     * @see savePoint()
     */
    public static function lockPoint($exclusiveLock, $exclusiveLockPrefix = '')
    {
        if (($exclusiveLock_int32 = \Anakeen\Core\Utils\Types::toInt32($exclusiveLock)) === false) {
            throw new \Anakeen\Database\Exception("DB0012", var_export($exclusiveLock, true));
        }
        $exclusiveLock = $exclusiveLock_int32;


        $idbid = intval(static::getDbid());
        if (empty(static::$savepoint[$idbid])) {
            throw new \Anakeen\Database\Exception("DB0011", $exclusiveLock, $exclusiveLockPrefix);
        }

        if ($exclusiveLockPrefix) {
            if (strlen($exclusiveLockPrefix) > 4) {
                throw new \Anakeen\Database\Exception("DB0010", $exclusiveLockPrefix);
            }
            $prefixLockId = unpack("i", str_pad($exclusiveLockPrefix, 4)) [1];
        } else {
            $prefixLockId = 0;
        }
        if (static::$masterLock === false) {
            static::query(sprintf('select pg_advisory_lock(0), pg_advisory_unlock(0), pg_advisory_xact_lock(%d,%d);', $exclusiveLock, $prefixLockId));
        }

        static::$lockpoint[$idbid][sprintf("%d-%s", $exclusiveLock, $exclusiveLockPrefix)] = array(
            $exclusiveLock,
            $prefixLockId
        );
    }

    /**
     * commit transaction save point
     *
     * @param string $point
     *
     * @return void
     * @throws \Dcp\Core\Exception
     */
    public static function commitPoint($point)
    {
        $idbid = intval(static::getDbid());

        $lastPoint = array_search($point, static::$savepoint[$idbid]);

        if ($lastPoint !== false) {
            static::$savepoint[$idbid] = array_slice(static::$savepoint[$idbid], 0, $lastPoint);
            static::query(sprintf('release savepoint "%s"', pg_escape_string($point)));
            if (count(static::$savepoint[$idbid]) == 0) {
                static::query("commit");
                static::$inTransition=false;
            }
        } else {
            throw new \Dcp\Core\Exception(sprintf("cannot commit unsaved point : %s", $point));
        }
    }

    /**
     * revert to transaction save point
     *
     * @param string $point revert point
     *
     * @return void
     * @throws \Dcp\Core\Exception
     */
    public static function rollbackPoint($point)
    {
        $idbid = intval(static::getDbid());
        if (isset(static::$savepoint[$idbid])) {
            $lastPoint = array_search($point, static::$savepoint[$idbid]);
        } else {
            $lastPoint = false;
        }
        if ($lastPoint !== false) {
            static::$savepoint[$idbid] = array_slice(static::$savepoint[$idbid], 0, $lastPoint);
            static::query(sprintf('rollback to savepoint "%s"', pg_escape_string($point)));
            if (count(static::$savepoint[$idbid]) == 0) {
                static::query("commit");
                static::$inTransition=false;
            }
        } else {
            throw new \Dcp\Core\Exception(sprintf("cannot rollback unsaved point : %s", $point));
        }
    }

    /**
     * set a database  master lock
     * the lock is free when explicit call with false parameter.
     * When a master lock is set,
     *
     * @param bool $useLock set lock (true) or unlock (false)
     *
     * @return void
     */
    public static function setMasterLock($useLock)
    {
        if ($useLock) {
            static::query('select pg_advisory_lock(0)');
        } else {
            static::query('select pg_advisory_unlock(0)');
        }

        static::$masterLock = (bool)$useLock;
    }

    /**
     * @param array    $values
     * @param   string $column
     * @param bool     $integer
     *
     * @return string like : mycol in ('a', 'b' , 'c')
     */
    public static function getSqlOrCond($values, $column, $integer = false)
    {
        $sql_cond = "";
        if (count($values) > 0) {
            if ($integer) { // for integer type
                $sql_cond = "$column in (";
                $sql_cond .= implode(",", $values);
                $sql_cond .= ")";
            } else { // for text type
                foreach ($values as & $v) {
                    $v = pg_escape_string($v);
                }
                $sql_cond = "$column in ('";
                $sql_cond .= implode("','", $values);
                $sql_cond .= "')";
            }
        }

        if (!$sql_cond) {
            $sql_cond="false";
        }
        return $sql_cond;
    }
}
