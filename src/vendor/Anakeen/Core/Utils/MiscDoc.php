<?php

namespace Anakeen\Core\Utils;

use Anakeen\Core\DbManager;

class MiscDoc
{
    public static function fixMultipleAliveDocument(\Doc & $doc)
    {
        if ($doc->id && $doc->fromid > 0) {
            DbManager::query(sprintf(
                "select id from only doc%d where initid=%d and locked != -1 order by id",
                $doc->fromid,
                $doc->initid
            ), $r);
            array_pop($r); // last stay alive
            if (count($r) > 0) {
                $rid = array();
                foreach ($r as $docInfo) {
                    DbManager::query(sprintf("update doc set locked= -1 where id=%d", $docInfo["id"]));
                    $rid[] = $docInfo["id"];
                    if ($docInfo["id"] == $doc->id) {
                        $doc->locked = -1;
                    }
                }
                $doc->addHistoryEntry(
                    sprintf(_("Fix multiple alive document #%s"), implode(', ', $rid)),
                    \DocHisto::WARNING
                );
                addWarningMsg(sprintf(_("Fix multiple alive revision for \"%s\""), $doc->getTitle()));
                global $action;
                $action->log->warning(sprintf(
                    _("Fix multiple alive document for \"%s\" #%s"),
                    $doc->getTitle(),
                    implode(', ', $rid)
                ));
            }
        }
    }

    public static function getFamFromId($id)
    {
        if (!($id > 0)) {
            return false;
        }
        if (!is_numeric($id)) {
            return false;
        }
        $dbid = DbManager::getDbId();
        $fromid = false;
        $result = pg_query($dbid, "select  fromid from docfam where id=$id;");

        if (pg_num_rows($result) > 0) {
            $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);
            $fromid = intval($arr["fromid"]);
        }

        return $fromid;
    }

    public static function computeVisibility($vis, $fvis, $ffvis = '')
    {
        if ($vis == "I") {
            return $vis;
        }
        if ($fvis == "H") {
            return $fvis;
        }
        if (($fvis == "R") && (($vis == "W") || ($vis == "U") || ($vis == "S"))) {
            return $fvis;
        }
        if (($fvis == "R") && ($vis == "O")) {
            return "H";
        }
        if (($fvis == "O") && ($vis == "W")) {
            return $fvis;
        }
        if (($fvis == "S") && (($vis == "W") || ($vis == "O"))) {
            return $fvis;
        }
        if ($fvis == "I") {
            return $fvis;
        }
        if ($fvis == 'U') {
            if ($ffvis && ($vis == 'W' || $vis == 'O' || $vis == 'S')) {
                if ($ffvis == 'S') {
                    return 'S';
                }
                if ($ffvis == 'R') {
                    return 'R';
                }
            }
        }

        return $vis;
    }
}
