<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\DbObj;
use Anakeen\Migration\Utils;
use Anakeen\Router\ApiV2Response;

/**
 * Class StructureTransfert
 * @package Anakeen\Routes\Migration\Database
 * @use by route /api/v2/migration/database/transfert/tables/{table}
 */
class TableTransfert
{
    protected $tableName;
    /**
     * @var DbObj $tableObject
     */
    protected $tableObject;
    protected $clearBefore = false;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->tableName = $args["table"];

        $this->tableObject = new  $this->tableName;

        $this->clearBefore = $request->getQueryParam("clear") === "all";
    }

    protected function doRequest()
    {
        $data = [];


        $data["count"] = count($this->transfertRequest($this->tableObject));

        return $data;
    }


    protected function transfertRequest(DbObj $tableObject)
    {

        $pgTable = pg_escape_identifier($tableObject->dbtable);

        Utils::importForeignTable($tableObject->dbtable);


        if ($this->clearBefore === true) {
            $sql = sprintf("delete from %s", $pgTable);
            DbManager::query($sql);
        }

        $qsql = <<<SQL
insert into %s (%s) select %s from dynacase.%s 
SQL;


        if (count($tableObject->id_fields) === 1) {
            $where = sprintf(
                " where %s not in (select %s from %s)",
                pg_escape_identifier($tableObject->id_fields[0]),
                pg_escape_identifier($tableObject->id_fields[0]),
                $pgTable
            );
            $qsql .= $where;
        }

        $qsql .= " returning " . $tableObject->id_fields[0];

        $fields = $tableObject->fields;
        $sql = sprintf(
            $qsql,
            $pgTable,
            implode(", ", array_values($fields)),
            implode(", ", array_values($fields)),
            $pgTable
        );

        print_r($sql . "\n");
        DbManager::query($sql, $ids, true);
        return $ids;
    }
}
