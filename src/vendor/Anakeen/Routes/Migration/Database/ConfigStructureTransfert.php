<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\DocAttr;
use Anakeen\Migration\Utils;
use Anakeen\Router\Exception;

class ConfigStructureTransfert extends DataElementTransfert
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
        Utils::importForeignTable("docfam");

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
        $enumIds = static::importStructureEnums($structureName);


        /**
         * Write PHP Class file
         */
        static::createBehaviorStub($structureName);

        DbManager::query("commit");
        return array_merge($ids, $fieldIds, $enumIds);
    }

    protected function createBehaviorStub($structureName)
    {
        $sql = sprintf(
            "select classname from docfam where name='%s'",
            pg_escape_string($structureName)
        );
        DbManager::query($sql, $classPath, true, true);
        $sql = sprintf(
            "select name from docfam where id=(select fromid from docfam where name='%s');",
            pg_escape_string($structureName)
        );
        $vendorName = ContextManager::getParameterValue("Migration", "VENDOR");
        if (!$vendorName) {
            throw new Exception("Migration VENDOR parameter is not set");
        }
        DbManager::query($sql, $parentName, true, true);
        $className = ucfirst(strtolower($structureName));

        $namePath = [$vendorName, $className];
        $className = sprintf("%sBehavior", $className);
        $vendorPath = sprintf("%s/vendor", ContextManager::getRootDirectory());
        $template = file_get_contents(__DIR__ . '/../../../Migration/StructureBehavior.php.mustache');

        $sql = sprintf(
            "update docfam set classname = E'%s\\\\%s' where name='%s'",
            implode("\\\\", $namePath),
            $className,
            pg_escape_string($structureName)
        );

        $stubPath = sprintf("%s/%s/%s.php", $vendorPath, implode("/", $namePath), $className);

        if ($parentName) {
            $extends = '\\SmartStructure\\' . ucfirst(strtolower($parentName));
        } else {
            $extends = '\\Anakeen\\SmartElement';
        }

        DbManager::query($sql);
        //print "$stubPath\n$sql\n";
        $mustache = new \Mustache_Engine();
        $stubBehaviorContent = $mustache->render($template, [
            "Classname" => $className,
            "Namespace" => implode("\\", $namePath),
            "Extends" => $extends,
            "OriginalClass" => $classPath,
            "structureName" => $structureName
        ]);
        Utils::writeFileContent($stubPath, $stubBehaviorContent);
    }

    protected function importStructureEnums($structureName)
    {

        Utils::importForeignTable("docenum");


        $sql = sprintf(
            "select * from docattr where type='enum' and docid=(select id from docfam where name='%s')",
            pg_escape_string($structureName)
        );

        DbManager::query($sql, $enums);
        if (!$enums) {
            return [];
        }
        $transferedEnum = [];
        foreach ($enums as $enum) {
            $attrObject = new DocAttr("", [$enum["docid"], $enum["id"]]);

            $enumSetName = sprintf("%s-%s", $structureName, $enum["id"]);
            $qsql = <<<SQL
insert into docenum ("name", key, label, parentkey, disabled, eorder) 
               select  '%s', key, label, parentkey, disabled, eorder from dynacase.docenum 
               where attrid='%s' and famid=%d returning key
SQL;
            $sql = sprintf($qsql, pg_escape_string($enumSetName), pg_escape_string($enum["id"]), $enum["docid"], pg_escape_string($structureName));
            //print "$sql\n";
            DbManager::query($sql, $keys);
            $transferedEnum = array_merge($transferedEnum, $keys);
            $attrObject->type = sprintf("enum(%s)", $enumSetName);
            $attrObject->modify();
        }

        return $transferedEnum;
    }

    protected function importStructureFields($structureName)
    {

        Utils::importForeignTable("docattr");
        $qsql = <<<SQL
insert into docattr (%s) 
select %s from dynacase.docattr where docid=(select id from docfam where name='%s') returning id
SQL;

        $attrObject = new DocAttr();


        foreach ($attrObject->fields as $field) {
            $fields[$field] = $field;
        }
        $fields["accessibility"] = "'ReadWrite'"; // Default Access is ReadWrite
        unset($fields["properties"]);// delete new properties

        $sql = sprintf(
            $qsql,
            implode(", ", array_keys($fields)),
            implode(", ", array_values($fields)),
            pg_escape_string($structureName)
        );

        DbManager::query($sql, $ids, true);
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

        DbManager::query($sql, $ids, true);
        if (!$ids) {
            throw new Exception(sprintf("Structure \"\%s\" not found", $structureName));
        }
        return $ids;
    }

    protected function importStructureDefValParam($structureName)
    {
        $sql = sprintf("select defval, param from dynacase.docfam where name='%s'", pg_escape_string($structureName));
        DbManager::query($sql, $config, false, true);


        if ($config["defval"]) {
            $defaultValues = self::explodeX($config["defval"]);
            $sql = sprintf("update docfam set defaultvalues='%s' where name='%s'", pg_escape_string(json_encode($defaultValues)), pg_escape_string($structureName));
            //print "$sql\n";
            DbManager::query($sql);
        }
        if ($config["param"]) {
            $param = self::explodeX($config["param"]);
            $sql = sprintf("update docfam set param='%s' where name='%s'", pg_escape_string(json_encode($param)), pg_escape_string($structureName));
            //print "$sql\n";
            DbManager::query($sql);
        }
    }

    protected static function getStructConfigMapping()
    {
        return ["dfldid" => "dfldid",
            "cfldid" => "cfldid",
            "ccvid" => "ccvid",
            "cprofid" => "cprofid",
            "ddocid" => "ddocid",
          //  "methods" => "methods",
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
