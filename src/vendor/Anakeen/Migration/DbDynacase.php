<?php

namespace Anakeen\Migration;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;

class DbDynacase extends DbManager
{
    protected static $pgConnection=null;
    protected static $dbRessource;

    public static function getDbAccess()
    {
        if (static::$pgConnection) {
            return static::$pgConnection;
        }

        static::$pgConnection = ContextManager::getParameterValue("Migration", "DYNACASE_DB");
        return static::$pgConnection;
    }
}