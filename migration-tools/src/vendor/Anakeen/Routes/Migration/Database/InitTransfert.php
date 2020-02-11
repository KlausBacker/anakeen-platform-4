<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Migration\Utils;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;

/**
 * Class StructureTransfert
 * @package Anakeen\Routes\Migration\Database
 * @use by route /api/v2/migration/database/transfert/structures/{structure}
 */
class InitTransfert
{
    protected $structureName;
    protected $structure;

    const delta = 800;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters();
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters()
    {
        $vendorName = ContextManager::getParameterValue("Migration", "VENDOR");
        $subDirName = ContextManager::getParameterValue("Migration", "MODULE");
        if (!$vendorName) {
            throw new Exception("Migration VENDOR parameter is not set");
        }
        if (!$subDirName) {
            throw new Exception("Migration MODULE parameter is not set");
        }
    }

    protected function doRequest()
    {
        $data = [];

        DbManager::query("create schema if not exists dynacase;");
        // Testing connexion with foreign server first
        Utils::importForeignTable("docfam");

        $tools = file_get_contents(__DIR__ . "/../../../Migration/Tools.sql");
        DbManager::query($tools);

        $this->moveRoles();
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
            $sqls = static::getSqlToMoveId($id, $id - self::delta);
            foreach ($sqls as $sql) {
                DbManager::query($sql);
            }
        }
        if ($ids) {
            $sql = sprintf("select id from docfam where id > %d", 999 - self::delta);
            DbManager::query($sql, $ids, true, false);
            foreach ($ids as $id) {
                $id = intval($id);
                $sql = sprintf("alter table doc%d rename to doc%d", $id + self::delta, $id);
                DbManager::query($sql);
                $sql = sprintf("drop sequence if exists seq_doc%d", $id + self::delta);
                DbManager::query($sql);
            }
        }
        DbManager::query("commit");
    }

    /**
     * Import platform id to use dynacase id
     */
    protected function moveIds()
    {
        // @TODO Need to restore constraint after
        $sql = "alter table docpermext drop constraint if exists docpermext_docid_check";
        DbManager::query($sql);
        $sql = "alter table docperm drop constraint  if exists docperm_docid_check";
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

    protected static function moveRoles()
    {
        $sql = <<<SQL
update users set id = -id where id > 9;
update groups set iduser = -iduser where iduser > 9;
update groups set idgroup = -idgroup where iduser > 9;
update doc127 set us_whatid = -(us_whatid::int) where us_whatid::int > 9;
update doc128 set us_whatid = -(us_whatid::int) where us_whatid::int > 9;
update doc130 set us_whatid = -(us_whatid::int) where us_whatid::int > 9;
update permission set id_user = -id_user where id_user > 9;
SQL;
        DbManager::query($sql);
    }


    /**
     * Call in Final Update
     * @throws \Anakeen\Database\Exception
     */
    public static function restoreRoles()
    {
        DbManager::query("select max(id) from users", $delta, true, true);

        $sql = <<<SQL
update users set id = (-id + %d) where id < -9;
update groups set iduser = (-iduser + %d) where iduser < -9;
update groups set idgroup = (-idgroup + %d) where iduser < -9;
update doc127 set us_whatid = (- us_whatid::int + %d) where us_whatid::int < -9;
update doc128 set us_whatid = (- us_whatid::int + %d) where us_whatid::int < -9;
update doc130 set us_whatid = (- us_whatid::int + %d) where us_whatid::int < -9;
update permission set id_user = (-id_user + %d) where id_user < -9;
SQL;
        DbManager::query(sprintf($sql, $delta, $delta, $delta, $delta, $delta, $delta, $delta));
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
        $sqls[] = sprintf("update doc set fallid=%d where fallid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docfam set ccvid=%d where ccvid=%d", $idTo, $idFrom);
        $sqls[] = sprintf("update docfam set cfallid=%d where cfallid=%d", $idTo, $idFrom);
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

        $sqls[] = sprintf(
            "update family.cvdoc set cv_mskid=array_replace(cv_mskid::int[], %d, %d)::int[]",
            $idFrom,
            $idTo
        );
        $sqls[] = sprintf(
            "update family.fieldaccesslayerlist set fall_layer=array_replace(fall_layer::int[], %d, %d)::int[]",
            $idFrom,
            $idTo
        );
        $sqls[] = sprintf(
            "update family.hubconfiguration set hub_station_id='%d' where hub_station_id::int = '%d'",
            $idTo,
            $idFrom
        );
        $sqls[] = sprintf(
            "update family.hubconfiguration set hub_execution_roles=array_replace(hub_execution_roles::int[], %d, %d)::int[]",
            $idFrom,
            $idTo
        );
        return $sqls;
    }
}
