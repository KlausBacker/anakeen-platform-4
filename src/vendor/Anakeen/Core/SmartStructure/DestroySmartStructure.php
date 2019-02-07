<?php


namespace Anakeen\Core\SmartStructure;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Exception;

class DestroySmartStructure
{
    public static function destroyFamily($idfam, $force = false)
    {
        $tdoc = SEManager::getRawDocument($idfam);
        if ($tdoc) {
            $resid = $tdoc["id"];
            $resname = $tdoc["name"];
            if (empty($tdoc["name"])) {
                throw new Exception("Structure has no name !!");
            }
            if ($tdoc["doctype"] !== "C") {
                throw new Exception("Not a structure !!");
            }

            $dbid = DbManager::getDbId();
            $tsql = array();
            if (!$force) {
                $tsql[] = "BEGIN;";
            }
            $tsql += array(
                sprintf("DELETE FROM fld WHERE childid IN (SELECT id FROM doc%d);", $resid),
                sprintf("DELETE FROM doc%d;", $resid),
                sprintf("DELETE FROM docname WHERE name = %s;", pg_escape_literal($resname)),
                sprintf("DELETE FROM docfrom WHERE fromid = %d;", $resid),
                sprintf("DELETE FROM docname WHERE fromid = %d;", $resid),
                sprintf("DELETE FROM docattr WHERE docid = %d;", $resid),
                sprintf("DELETE FROM docfam WHERE id = %d;", $resid),
                sprintf("DROP VIEW IF EXISTS family.%s;", pg_escape_identifier(strtolower($resname))),
                sprintf("DROP TABLE IF EXISTS doc%d;", $resid),
                sprintf("DROP SEQUENCE IF EXISTS seq_doc%d;", $resid)
            );
            if (!$force) {
                $tsql[] = "COMMIT;";
            }

            \Anakeen\Core\SmartStructure\SmartStructureImport::deleteGenFiles($tdoc["name"]);
            $res = "";
            foreach ($tsql as $sql) {
                $res = @pg_query($dbid, $sql);
                if (!$res) {
                    if (!$force) {
                        break;
                    }
                }
            }
            if ($res) {
                $data["messages"] = [];
                $data["messages"] += $tsql;
                $data["messages"] += array_merge($data["messages"], array(sprintf("Structure %s (id : %d) is destroyed.\n", $tdoc["name"], $tdoc["id"])));
                return $data;
            }
        } else {
            if (!is_numeric($idfam)) {
                \Anakeen\Core\SmartStructure\SmartStructureImport::deleteGenFiles($idfam);
            }
            throw new Exception(sprintf("Structure \"%s\" not found", $idfam));
        }
    }
}
