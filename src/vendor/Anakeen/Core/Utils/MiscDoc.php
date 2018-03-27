<?php

namespace Anakeen\Core\Utils;

use Anakeen\Core\DbManager;

class MiscDoc {
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
}