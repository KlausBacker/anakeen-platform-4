<?php

namespace Control\Internal;

use Anakeen\Core\DbManager;

require_once(__DIR__ . '/../../../include/class/Class.WIFF.php');

class Platform
{
    protected static $dbid=null;
    protected static function initPlatformContext()
    {
        if (! self::$dbid) {
            $rootPath=Context::getContext()->root;

            /** @noinspection PhpIncludeInspection */
            require_once $rootPath . '/vendor/Anakeen/autoload.php';
            $dbaccess = \Anakeen\Core\DbManager::getDbaccess();
            self::$dbid = pg_connect($dbaccess);
        }
    }

    public static function getUserStats() {
        $stats=[];

        self::initPlatformContext();
        DbManager::query("select count(*) from users where accounttype='U' and (status is null or status = 'A')", $stats["activeUserCount"], true, true);
        DbManager::query("select count(*) from users where accounttype='U' and status = 'D'", $stats["inactiveUserCount"], true, true);
        $stats["activeUserCount"]=intval($stats["activeUserCount"]);
        $stats["inactiveUserCount"]=intval($stats["inactiveUserCount"]);
        return $stats;
    }


    public static function getStatusInfo() {
        self::initPlatformContext();
        return \Anakeen\Script\System::getStatusInfo();
    }

}