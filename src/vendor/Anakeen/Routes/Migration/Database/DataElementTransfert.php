<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Migration\Utils;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class StructureTransfert
 * @package Anakeen\Routes\Migration\Database
 * @use by route /api/v2/migration/database/transfert/structures/{structure}
 */
class DataElementTransfert
{
    protected $structureName;
    /**
     * @var SmartStructure
     */
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

        $data["properties"]=$this->getProperties();
        return $data;
    }


    protected function getProperties()
    {
        if ($this->structure) {
            return [
                "id" => $this->structure->id,
                "name" => $this->structure->name,
                "title" => $this->structure->getTitle(),
            ];
        } else {
            return [
                "name" => $this->structureName
            ];
        }
    }

    protected static function transfertRequest(SmartStructure $structure)
    {

        Utils::importForeignTable("docfam");
        $sql = sprintf("select id from only dynacase.docfam where name='%s'", pg_escape_string($structure->name));
        DbManager::query($sql, $dynacaseId, true, true);


        Utils::importForeignTable(sprintf("doc%d", $dynacaseId));


        $propMapping = static::getPropMapping();
        $fields = $structure->getNormalAttributes();
        foreach ($fields as $field) {
            if (!$field->isMultiple()) {
                switch ($field->type) {
                    case "action":
                    case "menu":
                    case "array":
                        break;
                    default:
                        $propMapping[$field->id] = $field->id;
                }
            }

            if ($field->isMultiple() && !$field->isMultipleInArray()) {
                switch ($field->type) {
                    case "longtext":
                        $propMapping[$field->id] = sprintf("longtext_to_array(%s)", $field->id);
                        break;
                    case "time":
                        $propMapping[$field->id] = sprintf("text_to_array(%s)::time[]", $field->id);
                        break;
                    case "timestamp":
                        $propMapping[$field->id] = sprintf("text_to_array(%s)::timestamp[]", $field->id);
                        break;
                    case "date":
                        $propMapping[$field->id] = sprintf("text_to_array(%s)::date[]", $field->id);
                        break;
                    case "money":
                    case "double":
                        $propMapping[$field->id] = sprintf("text_to_array(%s)::float8[]", $field->id);
                        break;
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
