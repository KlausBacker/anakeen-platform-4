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
            if (!defined("DEFAULT_PUBDIR")) {
                throw new Exception("The autoloader is too old");
            }
            $dbaccess = \Anakeen\Core\DbManager::getDbaccess();
            self::$dbid = pg_connect($dbaccess);
        }
    }

    /**
     * @return array
     */
    public static function getStatusInfo()
    {
        try {
            self::initPlatformContext();
        } catch (Exception $e) {
            //Smart Data Engine is too old, so return []
            return [];
        }

        if (!function_exists("\Anakeen\Script\System::getStatusInfo")) {
            return [];
        }
        return \Anakeen\Script\System::getStatusInfo();
    }
}
