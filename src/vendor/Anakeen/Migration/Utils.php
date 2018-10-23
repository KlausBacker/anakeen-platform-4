<?php

namespace Anakeen\Migration;

use Anakeen\Core\DbManager;
use Anakeen\Router\Exception;

class Utils
{

    public static function writeFileContent($path, $content)
    {
        static::mkdirPath(dirname($path));

        if (!file_put_contents($path, $content)) {
            throw new Exception(sprintf("Cannot write file \%s\"", $path));
        }
    }

    public static function mkdirPath($path)
    {
        if ($path && !is_dir($path)) {
            static ::mkdirPath(dirname($path));
            if (!mkdir($path)) {
                throw new Exception(sprintf("Cannot mkdir \%s\"", $path));
            }
        }
    }

    public static function importForeignTable($tableName)
    {
        $sql = sprintf("select ftrelid from pg_foreign_table where 'table_name=%s' = any(ftoptions)", $tableName);
        DbManager::query($sql, $succeed, true);

        if (!$succeed) {
            $sql = sprintf("IMPORT FOREIGN SCHEMA public LIMIT TO (%s) FROM SERVER dynacase into dynacase;", pg_escape_identifier($tableName));
            DbManager::query($sql);

            //print "$sql\n";
        }
    }
}
