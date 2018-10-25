<?php

namespace Anakeen\Migration;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Router\Exception;
use Anakeen\Routes\Migration\Database\ConfigStructureTransfert;

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
            static::mkdirPath(dirname($path));
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

            print "$sql\n";
        }
    }

    public static function wgetDynacase($url)
    {
        // create curl resource
        $ch = curl_init();

        $baseUrl = ContextManager::getParameterValue("Migration", "DYNACASE_URL");
        $password = ContextManager::getParameterValue("Migration", "DYNACASE_PASSWORD");
        curl_setopt($ch, CURLOPT_USERPWD, "admin:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $baseUrl = rtrim($baseUrl, "/");
        $url = ltrim($url, "/");

        $sendUrl = $baseUrl . '/' . $url;
        // set url

        print "\n$sendUrl\n";
        curl_setopt($ch, CURLOPT_URL, $sendUrl);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        $data=json_decode($output, true);

        if ($data) {
            return $data;
        }
        return $output;

    }
}
