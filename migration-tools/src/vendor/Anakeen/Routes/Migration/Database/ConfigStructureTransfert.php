<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\Callables\ParseFamilyFunction;
use Anakeen\Core\SmartStructure\DocAttr;
use Anakeen\Core\Utils\Postgres;
use Anakeen\Migration\Utils;
use Anakeen\Router\Exception;

class ConfigStructureTransfert extends DataElementTransfert
{
    const SMART_STRUCTURES = "SmartStructures";
    protected static $vendorName;
    /**
     * @var string
     */
    protected static $subDirName;

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

        self::$vendorName = ContextManager::getParameterValue("Migration", "VENDOR");
        self::$subDirName = ContextManager::getParameterValue("Migration", "MODULE");
        if (!self::$vendorName) {
            throw new Exception("Migration VENDOR parameter is not set");
        }
        if (!self::$subDirName) {
            throw new Exception("Migration MODULE parameter is not set");
        }
        $data["count"] = count($this->transfertConfig($this->structureName));

        $data["properties"] = $this->getProperties();

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

    protected static function getBehaviorPath($structureName)
    {

        $structName = ucfirst(strtolower($structureName));

        $namePath = [self::$vendorName, self::SMART_STRUCTURES, $structName];
        $className = sprintf("%sBehavior", $structName);
        $vendorPath = sprintf("%s/vendor", ContextManager::getRootDirectory());
        if (self::$subDirName) {
            $namePath = [self::$vendorName, self::$subDirName, self::SMART_STRUCTURES, $structName];
        }
        return sprintf("%s/%s/%s.php", $vendorPath, implode("/", $namePath), $className);
    }

    protected static function getEnumPath($enumClassName)
    {
        $namePath = [self::$vendorName, "Enums"];
        $vendorPath = sprintf("%s/vendor", ContextManager::getRootDirectory());
        if (self::$subDirName) {
            $namePath = [self::$vendorName, self::$subDirName, "Enums"];
        }
        return sprintf("%s/%s/%s.php", $vendorPath, implode("/", $namePath), $enumClassName);
    }

    protected static function getBehaviorTemplateContent()
    {
        return file_get_contents(__DIR__ . '/../../../Migration/StructureBehavior.php.mustache');
    }
    protected static function getEnumTemplateContent()
    {
        return file_get_contents(__DIR__ . '/../../../Migration/EnumCallable.php.mustache');
    }

    protected static function createBehaviorStub($structureName)
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

        DbManager::query($sql, $parentName, true, true);
        $structDir = ucfirst(strtolower($structureName));

        if (self::$subDirName) {
            $namePath = [self::$vendorName, self::$subDirName, self::SMART_STRUCTURES, $structDir];
        } else {
            $namePath = [self::$vendorName, self::SMART_STRUCTURES, $structDir];
        }
        $className = sprintf("%sBehavior", $structDir);
        $template = static::getBehaviorTemplateContent();

        $sql = sprintf(
            "update docfam set classname = E'%s\\\\%s' where name='%s'",
            implode("\\\\", $namePath),
            $className,
            pg_escape_string($structureName)
        );

        DbManager::query($sql);


        $sql = sprintf(
            "update docfam set atags = atags || E'{\"vendor\":\"%s\"}' where name='%s'",
            self::$vendorName,
            pg_escape_string($structureName)
        );
        DbManager::query($sql);

        $stubPath = static::getBehaviorPath($structureName);

        if ($parentName) {
            $extends = '\\SmartStructure\\' . ucfirst(strtolower($parentName));
        } else {
            $extends = '\\Anakeen\\SmartElement';
        }

        $mustache = new \Mustache_Engine();
        $stubBehaviorContent = $mustache->render($template, [
            "VENDOR" => self::$vendorName,
            "Classname" => $className,
            "Namespace" => implode("\\", $namePath),
            "Extends" => $extends,
            "OriginalClass" => $classPath,
            "StructureName" => $structDir,
            "structureName" => $structureName
        ]);
        Utils::writeFileContent($stubPath, $stubBehaviorContent);
    }

    protected static function createEnumStub($enumClassName, $enumFunctionName, $attrInfo)
    {
        $sql = sprintf(
            "select classname from docfam where name='%s'",
            pg_escape_string($enumClassName)
        );
        DbManager::query($sql, $classPath, true, true);
        $sql = sprintf(
            "select name from docfam where id=(select fromid from docfam where name='%s');",
            pg_escape_string($enumClassName)
        );

        DbManager::query($sql, $parentName, true, true);
        $structDir = ucfirst(strtolower($enumClassName));

        if (self::$subDirName) {
            $namePath = [self::$vendorName, self::$subDirName, "Enums", $structDir];
        } else {
            $namePath = [self::$vendorName, "Enums", $structDir];
        }
        $template = static::getEnumTemplateContent();

        $stubPath = static::getEnumPath($enumClassName);

        $mustache = new \Mustache_Engine();
        $stubBehaviorContent = $mustache->render($template, [
            "VENDOR" => self::$vendorName,
            "EnumCallableFunction" => $enumFunctionName,
            "Namespace" => implode("\\", $namePath),
            "EnumCallableClass" => $enumClassName,
            "AttrId" => $attrInfo["id"],
            "AttrPhpFile" => $attrInfo["phpfile"],
            "AttrPhpFunc" => $attrInfo["phpfunc"],
            "structureName" => $enumClassName
        ]);
        Utils::writeFileContent($stubPath, $stubBehaviorContent);
    }

    protected static function importStructureEnums($structureName)
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
            if ($enum["phpfunc"] && $enum["phpfile"] && strlen($enum["phpfile"]) > 2) {
                // Callable enum
                $ft = new ParseFamilyFunction();
                $ft->parse($enum["phpfunc"], true);

                if ($ft->functionName === "linkenum" &&  $enum["phpfile"]==="fdl.php") {
                    // linkenum special case
                    $enumRelname=sprintf("%s-%s", $ft->inputs[0]->name, $ft->inputs[1]->name);

                    $sql = sprintf(
                        "update docattr set type='enum(\"%s\")', phpfunc = null, phpfile=null where id = '%s'  and docid=%d",
                        pg_escape_string($enumRelname),
                        pg_escape_string($enum["id"]),
                        $enum["docid"]
                    );
                    DbManager::query($sql);
                } else {
                    $basePhpFile = strtok($enum["phpfile"], ".");

                    $enumClassName = sprintf(
                        "%s\%s\Enums\%s::%s",
                        self::$vendorName,
                        self::$subDirName,
                        $basePhpFile,
                        $ft->functionName
                    );
                    $enumSetName = sprintf("%s-%s", $structureName, $enum["id"]);

                    $sql = sprintf("delete from docenum where name = '%s'", pg_escape_string($enumSetName));
                    DbManager::query($sql);
                    $sql = sprintf(
                        "insert into docenum (\"name\", key, label, parentkey, disabled, eorder) values ('%s', E'%s', '', '%s', null, 1) ",
                        pg_escape_string($enumSetName),
                        pg_escape_string($enumClassName),
                        \Anakeen\Core\EnumManager::CALLABLEKEY
                    );
                    DbManager::query($sql);

                    $sql = sprintf(
                        "update docattr set type='enum(\"%s\")', phpfunc = null, phpfile=null where id = '%s'  and docid=%d",
                        pg_escape_string($enumSetName),
                        pg_escape_string($enum["id"]),
                        $enum["docid"]
                    );
                    DbManager::query($sql);

                    self::createEnumStub($basePhpFile, $ft->functionName, $enum);
                }
            } else {
                // Static enum
                $attrObject = new DocAttr("", [$enum["docid"], $enum["id"]]);

                $enumSetName = sprintf("%s-%s", $structureName, $enum["id"]);

                $sql = sprintf("delete from docenum where name = '%s'", pg_escape_string($enumSetName));
                DbManager::query($sql);

                $qsql = <<<SQL
insert into docenum ("name", key, label, parentkey, disabled, eorder) 
               select  '%s', key, label, parentkey, disabled, eorder from dynacase.docenum 
               where attrid='%s' and famid=%d returning key
SQL;
                $sql = sprintf(
                    $qsql,
                    pg_escape_string($enumSetName),
                    pg_escape_string($enum["id"]),
                    $enum["docid"],
                    pg_escape_string($structureName)
                );
                DbManager::query($sql, $keys);
                $transferedEnum = array_merge($transferedEnum, $keys);
                $attrObject->type = sprintf("enum(\"%s\")", $enumSetName);
                $attrObject->modify();
            }
        }

        // Clean declaration of enum
        $sql = sprintf(
            "update docattr set phpfunc = null where phpfile is null and type ~ '^enum' and docid=(select id from docfam where name='%s')",
            pg_escape_string($structureName)
        );
        DbManager::query($sql);
        return $transferedEnum;
    }

    protected static function importStructureFields($structureName)
    {
        $qsql = <<<SQL
insert into docattr (%s) 
select %s from dynacase.docattr where docid=(select id from docfam where name='%s') returning id
SQL;

        $attrObject = new DocAttr();

        $fields = [];
        foreach ($attrObject->fields as $field) {
            $fields[$field] = $field;
        }

        unset($fields["properties"]);// delete new properties
        unset($fields["accessibility"]);
        $sql = sprintf(
            $qsql,
            implode(", ", array_keys($fields)),
            implode(", ", array_values($fields)),
            pg_escape_string($structureName)
        );

        DbManager::query($sql, $ids, true);

        // Delete menu and action
        $sql = "delete from docattr where type='menu' or type='action'";
        DbManager::query($sql);


        // Delete autogenerate attr
        $sql = "delete from docattr where options ~ 'autotitle=yes';";
        DbManager::query($sql);

        // Delete autogenerate attr
        $sql = "delete from docattr where options ~ 'autocreated=yes';";
        DbManager::query($sql);

        // Delete MODATTR without father
        $sql = "delete from docattr where id ~ '^:' and substring(id,2) not in (select id from docattr)";
        DbManager::query($sql);

        // Default Access is ReadWrite
        $sql = "update docattr set accessibility='ReadWrite' where accessibility is null and id !~ '^:'";
        DbManager::query($sql);


        // Delete old autocomplete
        $sql = "update docattr set phpfile=null, phpfunc=null  where phpfile like '%.php' and type !~ '^enum';";
        DbManager::query($sql);

        // Thesaurus are only docid
        $sql = "update docattr set type = 'docid(\"THCONCEPT\")'  where type ~ 'thesaurus';";
        DbManager::query($sql);

        self::reorderFields($structureName, "tab");
        self::reorderFields($structureName);
        return $ids;
    }

    protected static function reorderFields($structureName, $typeFilter = '')
    {
        DbManager::query(
            sprintf("select id from docfam where name='%s'", pg_escape_string($structureName)),
            $famid,
            true,
            true
        );

        if ($typeFilter) {
            $condType = sprintf("type = '%s'", pg_escape_string($typeFilter));
        } else {
            $condType = "type != 'tab'";
        }
        $fids = ConfigStructureTransfert::getFromids($famid);
        $fids[] = $famid;
        $sql = sprintf(
            "select * from docattr where ordered is not null and docid in (%s) and id !~ '^:' and %s order by ordered",
            implode(",", $fids),
            $condType
        );
        DbManager::query($sql, $results);

        $attrData = [];

        foreach ($results as $ka => $attr) {
            $attrData[$attr["id"]] = $attr;
        }
        $sql = sprintf(
            "select * from docattr where ordered is not null and docid in (%s) and id ~ '^:' and %s order by docid, ordered",
            implode(",", $fids),
            $condType
        );
        DbManager::query($sql, $modAttrs);


        foreach ($modAttrs as $ka => $attr) {
            $attrid = trim($attr["id"], ":");

            if (!empty($attr["frameid"]) && $attrData[$attrid]["frameid"] !== $attr["frameid"]) {
                $attrData[$attrid]["frameid"] = $attr["frameid"];
            }
            if ($attrData[$attrid]["ordered"] !== $attr["ordered"]) {
                if ($attr["docid"] === $famid) {
                    $modAttr = $attrData[$attrid];
                    $modAttr["ordered"] = $attr["ordered"];
                    $modAttr["docid"] = $attr["docid"];
                    $modAttr["id"] = $attr["id"];
                    // add to the end
                    unset($attrData[$attrid]);
                    $attrData[$attr["id"]] = $modAttr;
                } else {
                    $attrData[$attrid]["ordered"] = $attr["ordered"];
                }
            }
        }

        uasort($attrData, function ($a, $b) {
            if ($a["ordered"] > $b["ordered"]) {
                return 1;
            }
            if ($a["ordered"] < $b["ordered"]) {
                return -1;
            }
            return 0;
        });

        foreach ($attrData as $attr) {
            if ($attr["docid"] === $famid && $attr["ordered"]) {
                // find previous sibling
                $inhPreviousSibling = self::getPreviousSibling($attr, $attrData);
                if (!$inhPreviousSibling) {
                    if (self::getNextSibling($attr, $attrData)) {
                        $previousSibling = self::getPreviousSibling($attr, $attrData, true);
                        if (!$previousSibling) {
                            self::setRelativeOrder($attr, "::first", $structureName);
                        } else {
                            // insert after
                            self::setRelativeOrder($attr, trim($previousSibling["id"], ":"), $structureName);
                        }
                    } else {
                        self::setRelativeOrder($attr, "::auto", $structureName);
                    }
                } else {
                    $previousSibling = self::getPreviousSibling($attr, $attrData, true);
                    if (!$previousSibling) {
                        self::setRelativeOrder($attr, "::first", $structureName);
                    } else {
                        // insert after
                        self::setRelativeOrder($attr, trim($previousSibling["id"], ":"), $structureName);
                    }
                }
            }
        }
    }

    protected static function getPreviousSibling(array $refAttr, array $attrs, $searchItself = false)
    {
        $previous = null;
        if ($refAttr["id"][0] === ":") {
            // $refAttr["id"] = trim($refAttr["id"], ":");
            $searchItself = true;
        }
        foreach ($attrs as $attr) {
            if ($attr["id"] === $refAttr["id"]) {
                break;
            }

            if ($attr["frameid"] === $refAttr["frameid"] && ($searchItself || ($attr["docid"] !== $refAttr["docid"]))) {
                $previous = $attr;
            }
        }
        return $previous;
    }

    protected static function getNextSibling(array $refAttr, array $attrs)
    {
        return self::getPreviousSibling($refAttr, array_reverse($attrs));
    }

    protected static function setRelativeOrder(array $refAttr, $relativeOrder, $structureName)
    {
        printf("%-40s | %-50s | %s\n", $structureName, $refAttr["id"], $relativeOrder);

        $refAttr["options"] = preg_replace("/relativeOrder=[^|]*/", "", $refAttr["options"] ?? "");
        if (empty($refAttr["options"])) {
            $refAttr["options"] = '';
        } else {
            $refAttr["options"] .= "|";
        }
        $refAttr["options"] .= sprintf("relativeOrder=%s", $relativeOrder);

        $sql = sprintf(
            "update docattr set options='%s' where id='%s' and docid='%d'",
            pg_escape_string($refAttr["options"]),
            pg_escape_string($refAttr["id"]),
            $refAttr["docid"]
        );
        DbManager::query($sql);
    }

    public static function getFromids(int $structureId)
    {
        DbManager::query(sprintf("select getFromids(%d)", $structureId), $fromids, true, true);
        return Postgres::stringToArray($fromids);
    }

    protected static function importStructureProperties($structureName)
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

    protected static function importStructureDefValParam($structureName)
    {
        $sql = sprintf("select defval, param from dynacase.docfam where name='%s'", pg_escape_string($structureName));
        DbManager::query($sql, $config, false, true);


        if ($config["defval"]) {
            $defaultValues = self::explodeX($config["defval"]);
            $sql = sprintf(
                "update docfam set defaultvalues='%s' where name='%s'",
                pg_escape_string(json_encode($defaultValues)),
                pg_escape_string($structureName)
            );
            //print "$sql\n";
            DbManager::query($sql);
        }
        if ($config["param"]) {
            $param = self::explodeX($config["param"]);
            $sql = sprintf(
                "update docfam set param='%s' where name='%s'",
                pg_escape_string(json_encode($param)),
                pg_escape_string($structureName)
            );
            //print "$sql\n";
            DbManager::query($sql);
        }
    }

    protected static function getStructConfigMapping()
    {
        return [
            "dfldid" => "dfldid",
            "cfldid" => "cfldid",
            "ccvid" => "ccvid",
            "cprofid" => "cprofid",
            "ddocid" => "ddocid",
            //  "methods" => "methods",
            "schar" => "schar"
        ];
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
