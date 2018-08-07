<?php

namespace Anakeen\Core\Utils;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\SmartElement;

class MiscDoc
{
    public static function fixMultipleAliveDocument(\Anakeen\Core\Internal\SmartElement & $doc)
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
                \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("Fix multiple alive revision for \"%s\""), $doc->getTitle()));

                \Anakeen\LogManager::warning(sprintf(
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

    /**
     * Parse a docid's single or multiple value and resolve logical name references
     *
     * The function can report unknown logical names and can take an additional list of
     * known logical names to not report
     *
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $oattr
     * @param string                                       $avalue              docid's raw value
     * @param array                                        $unknownLogicalNames Return list of unknown logical names
     * @param array                                        $knownLogicalNames   List of known logical names that should not be reported as unknown in $unknownLogicalNames
     *
     * @return int|string The value with logical names replaced by their id
     */
    public static function resolveDocIdLogicalNames(
        \Anakeen\Core\SmartStructure\NormalAttribute & $oattr,
        $avalue,
        &$unknownLogicalNames = array(),
        &$knownLogicalNames = array()
    ) {
        $res = $avalue;
        if (!is_numeric($avalue)) {
            if (!$oattr->isMultiple()) {
                if ($oattr->getOption("docrev", "latest") == "latest") {
                    $res = \Anakeen\Core\SEManager::getInitidFromName($avalue);
                } else {
                    $res = \Anakeen\Core\SEManager::getIdFromName($avalue);
                }
                if (!$res && !in_array($avalue, $knownLogicalNames)) {
                    $unknownLogicalNames[] = $avalue;
                }
            } else {
                if (is_array($avalue)) {
                    $tnames=$avalue;
                } else {
                    $tnames = SmartElement::rawValueToArray($avalue);
                }

                $tids = array();
                foreach ($tnames as $lname) {
                    if (is_array($lname)) {
                        $mids = $lname;
                    } else {
                        $mids = [$lname];
                    }
                    $tlids = array();
                    foreach ($mids as $llname) {
                        if (!is_numeric($llname)) {
                            if ($oattr->getOption("docrev", "latest") == "latest") {
                                $llid = \Anakeen\Core\SEManager::getInitidFromName($llname);
                            } else {
                                $llid = \Anakeen\Core\SEManager::getIdFromName($llname);
                            }
                            if (!$llid && !in_array($llname, $knownLogicalNames)) {
                                $unknownLogicalNames[] = $llname;
                            }
                            $tlids[] = $llid ? $llid : $llname;
                        } else {
                            $tlids[] = $llname;
                        }
                    }
                    if (is_array($lname)) {
                        $tids[] = $lname;
                    } else {
                        $tids[] = $lname[0];
                    }
                }

                $res = $tids;
            }
        }
        return $res;
    }
}
