<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Delete family document and its documents
 *
 * @subpackage
 */
/**
 */
global $action;


$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Delete family document and its documents");
$docid = $usage->addRequiredParameter("famid", "special docid");
$force = $usage->addHiddenParameter("force", "force without transaction");

$transaction = $usage->addEmptyParameter("transaction", "abort deletion if one of query failed");
if (!$force) {
    $force = !$transaction;
} else {
    $force = ($force == "yes");
}
$usage->verify();


DestroySmartStructure::destroyFamily($docid, $force);


class DestroySmartStructure
{
    public static function destroyFamily($idfam, $force = false)
    {
        $tdoc = Anakeen\Core\DocManager::getRawDocument($idfam);
        if ($tdoc) {
            $resid = $tdoc["id"];
            $resname = $tdoc["name"];
            if (empty($tdoc["name"])) {
                throw new Exception("Family has no name !!");
            }
            if ($tdoc["doctype"] !== "C") {
                throw new Exception("Not a family !!");
            }

            print "Destroying [" . $tdoc["title"] . "(" . $tdoc["name"] . ")]\n";
            $dbid = \Anakeen\Core\DbManager::getDbId();
            $tsql = array();
            if (!$force) {
                $tsql[] = "BEGIN;";
            }
            $tsql += array(
                sprintf("DELETE FROM fld WHERE childid IN (SELECT id FROM doc%d);", $resid),
                sprintf("DELETE FROM doc%d;", $resid),
                sprintf("DELETE FROM docname WHERE name = %s;", pg_escape_literal($resname)),
                sprintf("DELETE FROM docfrom WHERE fromid = %d;", $resid),
                sprintf("DELETE FROM docattr WHERE docid = %d;", $resid),
                sprintf("DELETE FROM docfam WHERE id = %d;", $resid),
                sprintf("DROP VIEW IF EXISTS family.%s;", pg_escape_identifier(strtolower($resname))),
                sprintf("DROP TABLE IF EXISTS doc%d;", $resid),
                sprintf("DROP SEQUENCE IF EXISTS seq_doc%d;", $resid)
            );
            if (!$force) {
                $tsql[] = "COMMIT;";
            }

            \Dcp\FamilyImport::deleteGenFiles($tdoc["name"]);
            $res = "";
            foreach ($tsql as $sql) {
                print "$sql\n";
                $res = @pg_query($dbid, $sql);
                if (!$res) {
                    print pg_last_error() . "\n";
                    if (!$force) {
                        break;
                    }
                }
            }
            if ($res) {
                printf("Family %s (id : %d) is destroyed.\n", $tdoc["name"], $tdoc["id"]);
            }
        } else {
            if (!is_numeric($idfam)) {
                \Dcp\FamilyImport::deleteGenFiles($idfam);
            }
            throw new Exception(sprintf("Family \"%s\" not found", $idfam));
        }
    }
}
