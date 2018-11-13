<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\DbManager;
use Anakeen\Migration\Utils;
use Anakeen\Router\ApiV2Response;

/**
 * Class StructureTransfert
 * @package Anakeen\Routes\Migration\Database
 * @use by route /api/v2/migration/database/transfert/structures/{structure}
 */
class InitTransfert
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
    }

    protected function doRequest()
    {
        $data = [];

        $tools=file_get_contents(__DIR__."/../../../Migration/Tools.sql");
        DbManager::query($tools);


        $this->move1000();
        $this->moveIds();

        $sql = "delete from docread where id not in (select id from doc);";
        DbManager::query($sql);
        return $data;
    }

    protected function move1000()
    {
        DbManager::query("begin");
        $sql = "select id from doc where id > 999";
        DbManager::query($sql, $ids, true, false);
        foreach ($ids as $id) {
            $id = intval($id);
            $sqls = static::getSqlToMoveId($id, $id - 100);
            foreach ($sqls as $sql) {
                DbManager::query($sql);
            }
        }
        $sql = "select id from docfam where id > 899";
        DbManager::query($sql, $ids, true, false);
        foreach ($ids as $id) {
            $id = intval($id);
            $sql = sprintf("alter table doc%d rename to doc%d", $id + 100, $id);
            DbManager::query($sql);
            $sql = sprintf("alter sequence seq_doc%d rename to seq_doc%d", $id + 100, $id);
            DbManager::query($sql);
        }

        DbManager::query("commit");
    }

    /**
     * Import platform id to use dynacase id
     */
    protected function moveIds()
    {
        // @TODO Need to restore constraint after
        $sql = "alter table docpermext drop constraint docpermext_docid_check";
        DbManager::query($sql);
        $sql = "alter table docperm drop constraint docperm_docid_check";
        DbManager::query($sql);

        Utils::importForeignTable("docname");

        $sqls = ["begin"];
        $sqls[] = "SET CONSTRAINTS ALL DEFERRED";
        $sqls = array_merge($sqls, static::getSqlAlterTable());

        $sql = "select ln.name, dn.id as id32 ,  ln.id as id4 from docname as ln, dynacase.docname as dn  where ln.name = dn.name and ln.id <> dn.id order by dn.id";
        DbManager::query($sql, $results);

        $id32s = $id4s = [];
        foreach ($results as $result) {
            $id32s[] = $result["id32"];
            $id4s[] = $result["id4"];
        }
        foreach ($results as &$result2) {
            if (array_search($result2["id32"], $id4s) !== false) {
                $result2["isSimple"] = false;
            } else {
                if (array_search($result2["id4"], $id32s) !== false) {
                    $result2["isSimple"] = false;
                } else {
                    $result2["isSimple"] = true;
                }
            }
        }

        foreach ($results as $result) {
            $id4 = $result["id4"];
            $id32 = $result["id32"];

            if ($result["isSimple"] === true) {
                $sqls = array_merge($sqls, static::getSqlToMoveId($id4, $id32));
            } else {
                $sqls = array_merge($sqls, static::getSqlToMoveId($id4, -$id32));
            }
        }


        foreach ($results as $result) {
            $id32 = $result["id32"];
            if ($result["isSimple"] === false) {
                $sqls = array_merge($sqls, static::getSqlToMoveId(-$id32, $id32));
            }
        }

        $sqls[] = "commit";
        foreach ($sqls as $sql) {
            DbManager::query($sql);
        }
    }

    protected static function getSqlAlterTable()
    {
        $sqls = [];
        $sql = <<<SQL
select ln.name, ln.id as id4, dn.id as id32 
from docname as ln, dynacase.docname as dn  
where ln.name = dn.name and ln.id <> dn.id and ln.name in (select name from docfam) order by dn.id;
SQL;
        DbManager::query($sql, $structuresToMove);

        foreach ($structuresToMove as $structureToMove) {
            $id4 = $structureToMove["id4"];
            $id32 = $structureToMove["id32"];
            $sqls = array_merge($sqls, static::getSqlToMoveStructureId($structureToMove["name"], $id4, $id32));
        }
        return $sqls;
    }

    protected static function getSqlToMoveStructureId($name, $idFrom, $idTo)
    {
        $sqls[] = sprintf("drop view if exists family.doc%s", strtolower($name));
        $sqls[] = sprintf("alter table doc%d rename to doc%d", $idFrom, $idTo);

        return $sqls;
    }

    protected static function getSqlToMoveId($idFrom, $idTo)
    {
        //printf("mv $idFrom $idTo\n");
        $sqls[] = sprintf("update docread set id=%d where id=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update doc set id=%d where id=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update doc set initid=%d where initid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update doc set profid=%d where profid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update doc set dprofid=%d where dprofid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update doc set fromid=%d where fromid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update doc set cvid=%d where cvid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update doc set prelid=%d where prelid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update doc set wid=%d where wid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docfam set ccvid=%d where ccvid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docfam set cprofid=%d where cprofid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docfam set dfldid=%d where dfldid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docfam set cfldid=%d where cfldid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update dochisto set id=%d where id=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update dochisto set initid=%d where initid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docfrom set id=%d where id=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docfrom set fromid=%d where fromid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docname set id=%d where id=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docname set fromid=%d where fromid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docrel set sinitid=%d where sinitid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docrel set cinitid=%d where cinitid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docattr set docid=%d where docid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update fld set dirid=%d where dirid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update fld set childid=%d where childid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docperm set docid=%d where docid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docpermext set docid=%d where docid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update users set fid=%d where fid=%d", $idTo, $idFrom);

        $sqls[] = sprintf("update family.cvdoc set cv_mskid=array_replace(cv_mskid:int[], %d, %d):int[]", $idTo, $idFrom);
        $sqls[] = sprintf("update family.fieldaccesslayerlist set fall_layerid=array_replace(fall_layer:int[], %d, %d):int[]", $idTo, $idFrom);
        return $sqls;
    }
}
