<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\DocAttr;
use Anakeen\Router\Exception;

class OriginalStructureTransfert extends StructureTransfert
{
    protected function initParameters($args)
    {
        $this->structureName = $args["structure"];
        $this->structure = SEManager::getFamily($this->structureName);
        if ($this->structure) {
            throw new Exception(sprintf("Structure \"%s\" already exists", $this->structureName));
        }
    }

    protected function doRequest()
    {
        $data = [];


        $data["count"] = count($this->transfertConfig($this->structureName));

        return $data;
    }

    protected function transfertConfig($structureName)
    {
        static::importForeignTable("docfam");

        DbManager::query("begin");

        /**
         * Import structure properties
         */
        $ids = static::importStructureProperties($structureName);
        /**
         * Import defval and param : converted to json
         */
        static::importStructureDefValParam($structureName);

        /**
         * Import DOCATTR config
         */
        $fieldIds = static::importStructureFields($structureName);

        /**
         * Import ENUM config
         */

        /**
         * Write PHP Class file
         */


        DbManager::query("rollback");
        return array_merge($ids, $fieldIds);
    }


    protected function importStructureFields($structureName)
    {

        static::importForeignTable("docattr");
        $qsql = <<<SQL
insert into docattr (%s) 
select %s from dynacase.docattr where docid=(select id from docfam where name='%s') returning id
SQL;

        $attrObject = new DocAttr();
        $fields = $attrObject->fields;
        unset($fields[array_search("accessibility", $fields)]); // delete new properties
        unset($fields[array_search("properties", $fields)]); // delete new properties
        $sql = sprintf(
            $qsql,
            implode(", ", array_values($fields)),
            implode(", ", array_values($fields)),
            pg_escape_string($structureName)
        );

        print_r($sql . "\n");


        DbManager::query($sql, $ids, true);
        print_r($ids);
        return $ids;
    }

    protected function importStructureProperties($structureName)
    {
        $qsql = <<<SQL
insert into docfam (%s) 
select %s from dynacase.docfam where name='%s' returning id
SQL;

        $fields = array_merge(static::getPropMapping(), static::getStructConfigMapping());
        $sql = sprintf(
            $qsql,
            implode(", ", array_keys($fields)),
            implode(", ", array_values($fields)),
            pg_escape_string($structureName)
        );

        print_r($sql . "\n");


        DbManager::query($sql, $ids, true);
        return $ids;
    }

    protected function importStructureDefValParam($structureName)
    {
        $sql = sprintf("select defval, param from dynacase.docfam where name='%s'", pg_escape_string($structureName));
        DbManager::query($sql, $config, false, true);


        if ($config["defval"]) {
            $defaultValues = self::explodeX($config["defval"]);
            $sql = sprintf("update docfam set defaultvalues='%s' where name='%s'", pg_escape_string(json_encode($defaultValues)), pg_escape_string($structureName));
            print "$sql\n";
            DbManager::query($sql);
        }
        if ($config["param"]) {
            $param = self::explodeX($config["param"]);
            $sql = sprintf("update docfam set param='%s' where name='%s'", pg_escape_string(json_encode($param)), pg_escape_string($structureName));
            print "$sql\n";
            DbManager::query($sql);
        }
    }

    protected static function getStructConfigMapping()
    {
        return ["dfldid" => "dfldid",
            "cfldid" => "cfldid",
            "ccvid" => "ccvid",
            "ddocid" => "ddocid",
            "methods" => "methods",
            "schar" => "schar"];
    }

    private static function explodeX($sx)
    {
        $txval = array();
        $tdefattr = explode("][", substr($sx, 1, strlen($sx) - 2));
        foreach ($tdefattr as $k => $v) {
            $aid = substr($v, 0, strpos($v, '|'));
            $dval = substr(strstr($v, '|'), 1);
            if ($aid) {
                $txval[$aid] = $dval;
            }
        }
        return $txval;
    }
}
