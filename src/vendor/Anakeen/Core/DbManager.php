<?php

namespace Anakeen\Core;

class DbManager
{
    protected static $savepoint;
    protected static $masterLock;
    protected static $lockpoint;

    public static function getDbAccess()
    {
        static $pgConnection = null;

        if ($pgConnection) {
            return $pgConnection;
        }

        $configFile=ContextManager::getRootDirectory()."/".Settings::DbAccessFilePath;
        if (!file_exists($configFile)) {
            throw new \Dcp\Core\Exception("CORE0015", $configFile);
        }
        $pgservice_core=null;
        if (!include($configFile)) {
            throw new \Dcp\Core\Exception("CORE0016", $configFile);
        }


        if (!$pgservice_core) {
            throw new \Dcp\Core\Exception("CORE0016", $configFile);
        }
        $pgConnection = sprintf("service='%s'", $pgservice_core);

        return $pgConnection;
    }




    /**
     * @return null|resource
     * @throws \Dcp\Db\Exception
     */
    public static function getDbid()
    {
        static $dbRessource = null;

        if ($dbRessource) {
            return $dbRessource;
        }
        $dbRessource = pg_connect(self::getDbAccess());
        if (!$dbRessource) {
            // fatal error
            header('HTTP/1.0 503 DB connection unavalaible');
            throw new \Dcp\Db\Exception('DB0101', self::getDbAccess());
        }

        return $dbRessource;
    }

    /**
     * send simple query to database
     * @param string $query sql query
     * @param string|bool|array &$result query result
     * @param bool $singlecolumn set to true if only one field is return
     * @param bool $singleresult set to true is only one row is expected (return the first row). If is combined with singlecolumn return the value not an array, if no results and $singlecolumn is true then $results is false
     * @throws \Dcp\Db\Exception
     * @return void
     */
    public static function query($query, &$result = array(), $singlecolumn = false, $singleresult = false)
    {
        static $sqlStrict = null;

        $dbid = self::getDbid();

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
            throw new \Dcp\Db\Exception('DB0100', pg_last_error($dbid), $query);
        }
    }
    /**
     * set a database transaction save point
     * @param string $point point identifier
     * @throws \Dcp\Core\Exception
     * @return void
     */
    public static function savePoint($point)
    {
        $idbid = intval(self::getDbid());

        if (empty(self::$savepoint[$idbid])) {
            self::$savepoint[$idbid] = array(
                $point
            );
            self::query("begin");
        } else {
            self::$savepoint[$idbid][] = $point;
        }

        self::query(sprintf('savepoint "%s"', pg_escape_string($point)));
    }

    /**
     * Set a database transaction advisory lock
     *
     * - A transaction advisory lock can only be used within an existing
     *   transaction.  So, a transaction must have been explicitly opened
     *   by a call to  \Dcp\Db\Exception\Object::savePoint() before using  \Dcp\Db\Exception\Object::lockPoint().
     * - The lock is automatically released when the transaction is
     *   commited or rolled back.
     *
     * @param int    $exclusiveLock       Lock's identifier as a signed integer in the int32 range
     *                                    (i.e. in the range [-2147483648, 2147483647]).
     * @param string $exclusiveLockPrefix Lock's prefix string limited up to 4 bytes.
     *
     * @throws \Dcp\Db\Exception
     * @see savePoint()
     */
    public static function lockPoint($exclusiveLock, $exclusiveLockPrefix = '')
    {
        if (($exclusiveLock_int32 = \Dcp\Core\Utils\Types::to_int32($exclusiveLock)) === false) {
            throw new \Dcp\Db\Exception("DB0012", var_export($exclusiveLock, true));
        }
        $exclusiveLock = $exclusiveLock_int32;


        $idbid = intval(self::getDbid());
        if (empty(self::$savepoint[$idbid])) {
            throw new \Dcp\Db\Exception("DB0011", $exclusiveLock, $exclusiveLockPrefix);
        }

        if ($exclusiveLockPrefix) {
            if (strlen($exclusiveLockPrefix) > 4) {
                throw new \Dcp\Db\Exception("DB0010", $exclusiveLockPrefix);
            }
            $prefixLockId = unpack("i", str_pad($exclusiveLockPrefix, 4)) [1];
        } else {
            $prefixLockId = 0;
        }
        if (self::$masterLock === false) {
            self::query(sprintf('select pg_advisory_lock(0), pg_advisory_unlock(0), pg_advisory_xact_lock(%d,%d);', $exclusiveLock, $prefixLockId));
        }

        self::$lockpoint[$idbid][sprintf("%d-%s", $exclusiveLock, $exclusiveLockPrefix) ] = array(
            $exclusiveLock,
            $prefixLockId
        );
    }

    /**
     * commit transaction save point
     * @param string $point
     * @return void
     * @throws \Dcp\Core\Exception
     */
    public static function commitPoint($point)
    {
        $idbid = intval(self::getDbid());

        $lastPoint = array_search($point, self::$savepoint[$idbid]);

        if ($lastPoint !== false) {
            self::$savepoint[$idbid] = array_slice(self::$savepoint[$idbid], 0, $lastPoint);
            self::query(sprintf('release savepoint "%s"', pg_escape_string($point)));
            if (count(self::$savepoint[$idbid]) == 0) {
                self::query("commit");
            }
        } else {
            throw new \Dcp\Core\Exception(sprintf("cannot commit unsaved point : %s", $point));
        }
    }
    /**
     * revert to transaction save point
     * @param string $point revert point
     * @return void
     * @throws \Dcp\Core\Exception
     */
    public static function rollbackPoint($point)
    {
        $idbid = intval(self::getDbid());
        if (isset(self::$savepoint[$idbid])) {
            $lastPoint = array_search($point, self::$savepoint[$idbid]);
        } else {
            $lastPoint = false;
        }
        if ($lastPoint !== false) {
            self::$savepoint[$idbid] = array_slice(self::$savepoint[$idbid], 0, $lastPoint);
            self::query(sprintf('rollback to savepoint "%s"', pg_escape_string($point)));
            if (count(self::$savepoint[$idbid]) == 0) {
                self::query("commit");
            }
        } else {
            throw new \Dcp\Core\Exception(sprintf("cannot rollback unsaved point : %s", $point));
        }
    }
    /**
     * set a database  master lock
     * the lock is free when explicit call with false parameter.
     * When a master lock is set,
     * @param bool $useLock set lock (true) or unlock (false)
     * @return void
     */
    public static function setMasterLock($useLock)
    {
        if ($useLock) {
            self::query('select pg_advisory_lock(0)');
        } else {
            self::query('select pg_advisory_unlock(0)');
        }

        self::$masterLock = (bool)$useLock;
    }
}
