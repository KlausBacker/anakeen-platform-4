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


        $this->transfertRequest($this->structure);

        return $data;
    }


    protected function transfertRequest(SmartStructure $structure)
    {

        $sql = sprintf("select id from only docfam where name='%s'", pg_escape_string($structure->name));
        DbDynacase::query($sql, $dynacaseId, true, true);


        $sql = sprintf("DROP FOREIGN TABLE  IF EXISTS  dynacase.doc%d;", $dynacaseId);
        DbManager::query($sql);

        $sql = sprintf("IMPORT FOREIGN SCHEMA public LIMIT TO (doc%d) FROM SERVER dynacase into dynacase;", $dynacaseId);
        DbManager::query($sql);


        $propMapping = $this->getPropMapping();
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
                    default:
                        $propMapping[$field->id] = sprintf("text_to_array(%s)", $field->id);
                }
            }
        }


        if ($structure->usefor === "SP") {
             $propMapping["ba_desc"]="prf_desc";
        }

        $sql = sprintf(
            "insert into doc%d (%s) select %s from dynacase.doc%d where fromid=%d and id not in (select id from only doc%d ) and name not in (select name from docname where fromid=%d)",
            $structure->id,
            implode(", ", array_keys($propMapping)),
            implode(", ", array_values($propMapping)),
            $dynacaseId,
            $dynacaseId,
            $structure->id,
            $structure->id
        );

        print_r($sql . "\n");
        DbManager::query($sql);
    }

    protected function getPropMapping()
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
