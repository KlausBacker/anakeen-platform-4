<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Migration\DbDynacase;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class StructureTransfert
 * @package Anakeen\Routes\Migration\Database
 * @use by route /api/v2/migration/database/transfert/structures/{structure}
 */
class StructureTransfert
{
    protected $structureName;
    protected $structure;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters($args)
    {
        $this->structureName = $args["structure"];
        $this->structure = SEManager::getFamily($this->structureName);
        if (!$this->structure) {
            throw new Exception(sprintf("Structure \"%s\" not found", $this->structureName));
        }
    }

    protected function doRequest()
    {
        $data = [];


        $data["count"] = count($this->transfertRequest($this->structure));

        return $data;
    }

    protected static function importForeignTable($tableName)
    {
        $sql = sprintf("select ftrelid from pg_foreign_table where 'table_name=%s' = any(ftoptions)", $tableName);
        DbManager::query($sql, $succeed, true);

        if (!$succeed) {
            $sql = sprintf("IMPORT FOREIGN SCHEMA public LIMIT TO (%s) FROM SERVER dynacase into dynacase;", pg_escape_identifier($tableName));
            DbManager::query($sql);
        }
    }

    protected static function transfertRequest(SmartStructure $structure)
    {

        $sql = sprintf("select id from only docfam where name='%s'", pg_escape_string($structure->name));
        DbDynacase::query($sql, $dynacaseId, true, true);


        static::importForeignTable(sprintf("doc%d", $dynacaseId));


        $propMapping = static::getPropMapping();
        $fields = $structure->getNormalAttributes();
        foreach ($fields as $field) {
            if (!$field->isMultiple()) {
                switch ($field->type) {
                    case "array":
                        break;
                    default:
                        $propMapping[$field->id] = $field->id;
                }
            }

            if ($field->isMultiple() && !$field->isMultipleInArray()) {
                switch ($field->type) {
                    case "int":
                        $propMapping[$field->id] = sprintf("text_to_array(%s)::int[]", $field->id);
                        break;
                    case "xml":
                        $propMapping[$field->id] = sprintf("text_to_array(%s)::xml[]", $field->id);
                        break;
                    default:
                        $propMapping[$field->id] = sprintf("text_to_array(%s)", $field->id);
                }
            }
        }


        if ($structure->usefor === "SP") {
            $propMapping["ba_desc"] = "prf_desc";
        }
        if ($structure->name === "CVDOC") {
            $propMapping["ba_desc"] = "cv_desc";
            unset($propMapping["cv_primarymask"]);
        }

        $qsql = <<<SQL
insert into doc%d (%s) 
select %s from dynacase.doc%d where fromid=%d and id not in (
    select id from only doc%d ) and (name is null or name not in (select name from docname where fromid=%d
)) returning id
SQL;

        $sql = sprintf(
            $qsql,
            $structure->id,
            implode(", ", array_keys($propMapping)),
            implode(", ", array_values($propMapping)),
            $dynacaseId,
            $dynacaseId,
            $structure->id,
            $structure->id
        );

        print_r($sql . "\n");
        DbManager::query($sql, $ids, true);
        return $ids;
    }


    protected static function getPropMapping()
    {
        return ["id" => "id",
            "owner" => "owner",
            "title" => "title",
            "revision" => "revision",
            "version" => "version",
            "initid" => "initid",
            "fromid" => "fromid",
            "doctype" => "doctype",
            "locked" => "locked",
            "allocated" => "allocated",
            "icon" => "icon",
            "lmodify" => "lmodify",
            "profid" => "profid",
            "usefor" => "usefor",
            "cdate" => "cdate",
            "mdate" => "to_timestamp(revdate)",
            "classname" => "classname",
            "state" => "state",
            "wid" => "wid",
            "postitid" => "postitid",
            "cvid" => "cvid",
            "name" => "name",
            "dprofid" => "dprofid",
            "views" => "views",
            "atags" => "to_atags(atags)",
            "prelid" => "prelid",
            "confidential" => "confidential"];
    }
}
