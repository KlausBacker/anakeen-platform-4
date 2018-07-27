<?php

namespace Anakeen\SmartStructures\Dir;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\LogManager;

include_once("FDL/LegacyDocManager.php");

class DirLib
{
    public static function isSimpleFilter($sqlfilters)
    {
        if (!is_array($sqlfilters)) {
            return true;
        }
        static $props = false;

        if (!$props) {
            $d = new \Anakeen\Core\Internal\SmartElement();
            $props = $d->fields;
            $props = array_merge($props, $d->sup_fields);
            $props[] = "fulltext";
            $props[] = "svalues";
        }

        foreach ($sqlfilters as $k => $v) {
            $tok = ltrim($v, "(");
            $tok = ltrim($tok, " ");
            $tok = strtok($tok, " !=~@");
            if (!(strpos($tok, '.') > 0)) { // join is not in main table
                //if ($tok == "fulltext") return true;
                if (($tok !== false) && ($tok !== "true") && ($tok !== "false") && (!in_array(ltrim($tok, "("), $props))) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * compose query to serach document
     *
     * @param string          $dbaccess     database specification
     * @param string[]|string $dirid        the array of id or single id of folder where search document (0 => in all DB)
     * @param string          $fromid       for a specific familly (0 => all familly) (<0 strict familly)
     * @param array           $sqlfilters   array of sql filter
     * @param bool            $distinct     if want distinct without locked
     * @param bool            $latest       only latest document, set false if search in all revised doc
     * @param string          $trash        (no|only|also) search in trash or not
     * @param bool            $simplesearch set false if search is about specific attributes
     * @param int             $folderRecursiveLevel
     * @param string          $join         defined a join table like "id = dochisto(id)"
     * @param string          $only         set "only" to have only family (not descandent);
     * @return array|bool|string
     */
    public static function getSqlSearchDoc(
        $dbaccess,
        $dirid,
        $fromid,
        $sqlfilters = array(),
        $distinct = false,
        $latest = true,
        $trash = "",
        $simplesearch = false,
        $folderRecursiveLevel = 2,
        $join = '',
        $only = ""
    ) {
        if (($fromid != "") && (!is_numeric($fromid))) {
            preg_match('/^(?P<sign>-?)(?P<fromid>.+)$/', trim($fromid), $m);
            $fromid = $m['sign'] . \Anakeen\Core\SEManager::getFamilyIdFromName($m['fromid']);
        }
        $table = "doc";
        $qsql = array();
        if ($trash == "only") {
            $distinct = true;
        }
        if ($fromid == -1) {
            $table = "docfam";
        } elseif ($simplesearch) {
            $table = "docread";
        } elseif ($fromid < 0) {
            $only = "only";
            $fromid = -$fromid;
            $table = "doc$fromid";
        } else {
            if ($fromid != 0) {
                if (self::isSimpleFilter($sqlfilters) && (self::familyNeedDocread($dbaccess, $fromid))) {
                    $table = "docread";
                    $fdoc = \Anakeen\Core\SEManager::getFamily($fromid);
                    $sqlfilters[-4] = \Anakeen\Core\DbManager::getSqlOrCond(array_merge(array(
                        $fromid
                    ), array_keys($fdoc->GetChildFam())), "fromid", true);
                } else {
                    $table = "doc$fromid";
                }
            } elseif ($fromid == 0) {
                if (self::isSimpleFilter($sqlfilters)) {
                    $table = "docread";
                }
            }
        }
        $maintable = $table; // can use join only on search
        if ($join) {
            if (preg_match('/([a-z0-9_\-:]+)\s*(=|<|>|<=|>=)\s*([a-z0-9_\-:]+)\(([^\)]*)\)/', $join, $reg)) {
                $joinid = \Anakeen\Core\SEManager::getFamilyIdFromName($reg[3]);
                $jointable = ($joinid) ? "doc" . $joinid : $reg[3];

                $sqlfilters[] = sprintf("%s.%s %s %s.%s", $table, $reg[1], $reg[2], $jointable, $reg[4]); // "id = dochisto(id)";
                $maintable = $table;
                $table .= ", " . $jointable;
            } else {
                \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("search join syntax error : %s"), $join));
                return false;
            }
        }
        $maintabledot = ($maintable && $dirid == 0) ? $maintable . '.' : '';

        if ($distinct) {
            $selectfields = "distinct on ($maintable.initid) $maintable.*";
        } else {
            $selectfields = "$maintable.*";
            $sqlfilters[-2] = $maintabledot . "doctype != 'T'";
            ksort($sqlfilters);
        }
        $sqlcond = "true";
        ksort($sqlfilters);
        if (count($sqlfilters) > 0) {
            $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
        }

        if ($dirid == 0) {
            //-------------------------------------------
            // search in all Db
            //-------------------------------------------

            if ($trash == "only") {
                $sqlfilters[-3] = $maintabledot . "doctype = 'Z'";
                if ($latest) {
                    $sqlfilters[] = $maintabledot . "lmodify = 'D'";
                }
            } elseif ($trash == "also") {
                $sqlfilters[-3] = sprintf("(%slocked != -1 or %slmodify='D')", $maintabledot, $maintabledot);
            } elseif (!$fromid) {
                $sqlfilters[-3] = $maintabledot . "doctype != 'Z'";
            }

            if (($latest) && (($trash == "no") || (!$trash))) {
                $sqlfilters[-1] = $maintabledot . "locked != -1";
            }
            ksort($sqlfilters);
            if (count($sqlfilters) > 0) {
                $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
            }
            $qsql = "select $selectfields " . "from $only $table  " . "where  " . $sqlcond;
        } else {
            //-------------------------------------------
            // in a specific folder
            //-------------------------------------------
            $fld = null;
            if (!is_array($dirid)) {
                $fld = SEManager::getDocument($dirid);
            }
            if ((is_array($dirid)) || ($fld && $fld->defDoctype != 'S')) {
                $hasFilters = false;
                if ($fld && method_exists($fld, "getSpecificFilters")) {
                    /**
                     * @var \DocCollection $fld
                     */
                    $specFilters = $fld->getSpecificFilters();
                    if (is_array($specFilters) && (count($specFilters) > 0)) {
                        $sqlfilters = array_merge($sqlfilters, $specFilters);
                        $hasFilters = true;
                    }
                }

                //if ($fld->getRawValue("se_trash")!="yes") $sqlfilters[-3] = "doctype != 'Z'";
                if ($trash == "only") {
                    $sqlfilters[-1] = "locked = -1";
                    if ($latest) {
                        $sqlfilters[] = $maintabledot . "lmodify = 'D'";
                    }
                } elseif ($latest) {
                    $sqlfilters[-1] = "locked != -1";
                }
                ksort($sqlfilters);
                if (count($sqlfilters) > 0) {
                    $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
                }

                if (is_array($dirid)) {
                    $sqlfld = \Anakeen\Core\DbManager::getSqlOrCond($dirid, "dirid", true);
                    $qsql = "select $selectfields " . "from (select childid from fld where $sqlfld) as fld2 inner join $table on (initid=childid)  " . "where  $sqlcond ";
                } else {
                    $sqlfld = "dirid=$dirid and qtype='S'";
                    if ($fromid == 2) {
                        $sqlfld .= " and doctype='D'";
                    }
                    if ($fromid == 5) {
                        $sqlfld .= " and doctype='S'";
                    }
                    if ($hasFilters) {
                        $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
                        $qsql = "select $selectfields from $only $table where $sqlcond ";
                    } else {
                        $q = new \Anakeen\Core\Internal\QueryDb($dbaccess, \QueryDir::class);
                        $q->AddQuery($sqlfld);
                        $tfld = $q->Query(0, 0, "TABLE");
                        if ($q->nb > 0) {
                            $tfldid = array();
                            foreach ($tfld as $onefld) {
                                $tfldid[] = $onefld["childid"];
                            }
                            if (count($tfldid) > 1000) {
                                $qsql = "select $selectfields " . "from $table where initid in (select childid from fld where $sqlfld)  " . "and  $sqlcond ";
                            } else {
                                $sfldids = implode(",", $tfldid);
                                if ($table == "docread") {
                                    /*$qsql= "select $selectfields ".
                                    "from $table where initid in (select childid from fld where $sqlfld)  ".
                                    "and  $sqlcond ";	*/
                                    $qsql = "select $selectfields " . "from $table where initid in ($sfldids)  " . "and  $sqlcond ";
                                } else {
                                    /*$qsql= "select $selectfields ".
                                    "from (select childid from fld where $sqlfld) as fld2 inner join $table on (initid=childid)  ".
                                    "where  $sqlcond ";*/
                                    $qsql = "select $selectfields " . "from $only $table where initid in ($sfldids)  " . "and  $sqlcond ";
                                }
                            }
                        }
                    }
                    //$qsql= "select $selectfields "."from $table where $dirid = any(fldrels) and  "."  $sqlcond ";
                }
            } else {
                //-------------------------------------------
                // search familly
                //-------------------------------------------
                $docsearch = new \Anakeen\Core\Internal\QueryDb($dbaccess, \QueryDir::class);
                $docsearch->AddQuery("dirid=$dirid");
                $docsearch->AddQuery("qtype = 'M'");
                $ldocsearch = $docsearch->Query(0, 0, "TABLE");
                // for the moment only one query search
                if (($docsearch->nb) > 0) {
                    switch ($ldocsearch[0]["qtype"]) {
                        case "M": // complex query
                            // $sqlM=$ldocsearch[0]["query"];

                            /**
                             * @var \Anakeen\SmartStructures\Search\SearchHooks $fld
                             */
                            $fld = SEManager::getDocument($dirid);
                            if ($trash) {
                                $fld->setValue("se_trash", $trash);
                            } else {
                                $trash = $fld->getRawValue("se_trash");
                            }
                            $fld->folderRecursiveLevel = $folderRecursiveLevel;
                            $tsqlM = $fld->getQuery();
                            foreach ($tsqlM as $sqlM) {
                                if ($sqlM != false) {
                                    if (!preg_match("/doctype[ ]*=[ ]*'Z'/", $sqlM, $reg)) {
                                        if (($trash != "also") && ($trash != "only")) {
                                            $sqlfilters[-3] = "doctype != 'Z'";
                                        } // no zombie if no trash
                                        ksort($sqlfilters);
                                        foreach ($sqlfilters as $kf => $sf) { // suppress doubles
                                            if (strstr($sqlM, $sf)) {
                                                unset($sqlfilters[$kf]);
                                            }
                                        }
                                        if (count($sqlfilters) > 0) {
                                            $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
                                        } else {
                                            $sqlcond = "";
                                        }
                                    }
                                    if ($fromid > 0) {
                                        $sqlM = str_replace("from doc ", "from $only $table ", $sqlM);
                                    }
                                    if ($sqlcond) {
                                        $qsql[] = $sqlM . " and " . $sqlcond;
                                    } else {
                                        $qsql[] = $sqlM;
                                    }
                                }
                            }
                            break;
                    }
                } else {
                    return false; // no query avalaible
                }
            }
        }
        if (is_array($qsql)) {
            return $qsql;
        }
        return array(
            $qsql
        );
    }


    /**
     * system only - used by core return array of documents
     *
     * @param string     $dbaccess   database specification
     * @param array      $dirid      the array of id or single id of folder where search document
     * @param string     $start      the start index
     * @param string     $slice      the maximum number of returned document
     * @param array      $sqlfilters array of sql filter
     * @param int        $userid     the current user id
     * @param string     $qtype      LIST|TABLE the kind of return : list of object or list or values array
     * @param int|string $fromid     identifier of family document
     * @param bool       $distinct   if false all revision of the document are returned else only latest
     * @param string     $orderby    field order
     * @param bool       $latest     if true only latest else all revision
     * @param string     $trash      (no|only|also) search in trash or not
     * @param bool       $debug
     * @param int        $folderRecursiveLevel
     * @param string     $join
     * @param \SearchDoc $searchDoc  the SearchDoc object when getChildDoc is used by a SearchDoc object
     * @internal use searchDoc to get document collection
     * @see      SearchDoc
     * @return array
     */
    public static function internalGetDocCollection(
        $dbaccess,
        $dirid,
        $start = "0",
        $slice = "ALL",
        $sqlfilters = array(),
        $userid = 1,
        $qtype = "LIST",
        $fromid = "",
        $distinct = false,
        $orderby = "title",
        $latest = true,
        $trash = "",
        &$debug = null,
        $folderRecursiveLevel = 2,
        $join = '',
        \SearchDoc & $searchDoc = null
    ) {
        return self::_internalGetDocCollection(
            false,
            $dbaccess,
            $dirid,
            $start,
            $slice,
            $sqlfilters,
            $userid,
            $qtype,
            $fromid,
            $distinct,
            $orderby,
            $latest,
            $trash,
            $debug,
            $folderRecursiveLevel,
            $join,
            $searchDoc
        );
    }

    public static function _internalGetDocCollection(
        $returnSqlOnly,
        $dbaccess,
        $dirid,
        $start = "0",
        $slice = "ALL",
        $sqlfilters = array(),
        $userid = 1,
        $qtype = "LIST",
        $fromid = "",
        $distinct = false,
        $orderby = "title",
        $latest = true,
        $trash = "",
        &$debug = null,
        $folderRecursiveLevel = 2,
        $join = '',
        \SearchDoc & $searchDoc = null
    ) {
        // query to find child documents
        if (($fromid != "") && (!is_numeric($fromid))) {
            $fromid = \Anakeen\Core\SEManager::getFamilyIdFromName($fromid);
        }
        if ($fromid == 0) {
            $fromid = "";
        }
        if (($fromid == "") && ($dirid != 0) && ($qtype == "TABLE")) {
            /**
             * @var \DocCollection $fld
             */
            $fld = SEManager::getDocument($dirid);

            if ($fld->fromid == \Anakeen\Core\SEManager::getFamilyIdFromName("SSEARCH")) {
                /**
                 * @var \Anakeen\SmartStructures\Ssearch\SSearchHooks $fld
                 */
                return $fld->getDocList($start, $slice, $qtype, $userid);
            }

            if ($fld->defDoctype != 'S') {
                // try optimize containt of folder
                if (!$fld->hasSpecificFilters()) {
                    $td = self::getFldDoc($dbaccess, $dirid, $sqlfilters);
                    if (is_array($td)) {
                        return $td;
                    }
                }
            } else {
                if ($fld->getRawValue("se_famid")) {
                    $fromid = $fld->getRawValue("se_famid");
                    $fdoc = SEManager::getFamily(abs($fromid), true);
                    if (!$fdoc || !$fdoc->isAlive()) {
                        throw new \Dcp\Exception(sprintf(_('Family [%s] not found'), abs($fromid)));
                    }
                    unset($fdoc);
                }
            }
        } elseif ($dirid != 0) {
            $fld = SEManager::getDocument($dirid);
            if (($fld->defDoctype == 'S') && ($fld->getRawValue("se_famid"))) {
                $fromid = $fld->getRawValue("se_famid");
                $fdoc = SEManager::getFamily(abs($fromid), true);
                if (!$fdoc || !$fdoc->isAlive()) {
                    throw new \Dcp\Exception(sprintf(_('Family [%s] not found'), abs($fromid)));
                }
                unset($fdoc);
            }
        }
        if ($trash == "only") {
            $distinct = true;
        }
        //   xdebug_var_dump(xdebug_get_function_stack());
        if ($searchDoc) {
            $tqsql = $searchDoc->getQueries();
        } else {
            $tqsql = self::getSqlSearchDoc($dbaccess, $dirid, $fromid, $sqlfilters, $distinct, $latest, $trash, false, $folderRecursiveLevel, $join);
        }

        $tretdocs = array();
        if ($tqsql) {
            foreach ($tqsql as $k => & $qsql) {
                if ($qsql == false) {
                    unset($tqsql[$k]);
                }
            }
            $isgroup = (count($tqsql) > 1);
            foreach ($tqsql as & $qsql) {
                if ($fromid != -1) { // not families
                    if ($fromid != 0) {
                        if (preg_match('/from\s+docread/', $qsql) || $isgroup) {
                            $fdoc = new \DocRead($dbaccess);
                        } else {
                            $fdoc = SEManager::createDocument(abs($fromid), false);
                            if ($fdoc === false) {
                                throw new \Dcp\Exception(sprintf(_('Family [%s] not found'), abs($fromid)));
                            }
                        }
                    } else {
                        $fdoc = new \DocRead($dbaccess);
                    }
                    $tsqlfields = null;
                    if ($searchDoc) {
                        $tsqlfields = $searchDoc->getReturnsFields();
                    }
                    if ($tsqlfields == null) {
                        $tsqlfields = array();
                        if (isset($fdoc->fields) && is_array($fdoc->fields)) {
                            $tsqlfields = array_merge($tsqlfields, $fdoc->fields);
                        }
                        if (isset($fdoc->sup_fields) && is_array($fdoc->sup_fields)) {
                            $tsqlfields = array_merge($tsqlfields, $fdoc->sup_fields);
                        }
                    }
                    $maintable = '';
                    if (!$join && preg_match('/from\s+([a-z0-9])*,/', $qsql)) {
                        $join = true;
                    }
                    if ($join) {
                        if (preg_match('/from\s+([a-z0-9]*)/', $qsql, $reg)) {
                            $maintable = $reg[1];
                            $if = 0;
                            if ($maintable) {
                                foreach ($tsqlfields as $kf => $vf) {
                                    if ($if++ > 0) {
                                        $tsqlfields[$kf] = $maintable . '.' . $vf;
                                    }
                                }
                            }
                        }
                    }
                    $maintabledot = ($maintable) ? $maintable . '.' : '';
                    $sqlfields = implode(", ", $tsqlfields);
                    if ($userid > 1) { // control view privilege
                        // $qsql.= " and (${maintabledot}profid <= 0 or hasviewprivilege($userid, ${maintabledot}profid))";
                        $qsql .= sprintf(" and (%sviews && '%s')", $maintabledot, \SearchDoc::getUserViewVector($userid));
                        // no compute permission here, just test it
                        $qsql = str_replace("* from ", "$sqlfields  from ", $qsql);
                    } else {
                        $qsql = str_replace("* from ", "$sqlfields  from ", $qsql);
                    }
                    if ((!$distinct) && strstr($qsql, "distinct")) {
                        $distinct = true;
                    }
                    if ($start == "") {
                        $start = "0";
                    }
                    if ($distinct) {
                        if ($join || $maintable) {
                            $qsql .= " ORDER BY $maintable.initid, $maintable.id desc";
                        } else {
                            $qsql .= " ORDER BY initid, id desc";
                        }
                        if (!$isgroup) {
                            $qsql .= " LIMIT $slice OFFSET $start";
                        }
                    } else {
                        if (($fromid == "") && $orderby == "") {
                            $orderby = "title";
                        } elseif (substr($qsql, 0, 12) == "select doc.*") {
                            $orderby = "title";
                        }
                        if ($orderby == "" && (!$isgroup)) {
                            $qsql .= "  LIMIT $slice OFFSET $start;";
                        } else {
                            if ($searchDoc) {
                                $orderby = $searchDoc->orderby;
                            }
                            if (!$isgroup) {
                                if ($orderby != '') {
                                    $qsql .= " ORDER BY $orderby LIMIT $slice OFFSET $start;";
                                } else {
                                    $qsql .= " LIMIT $slice OFFSET $start;";
                                }
                            }
                        }
                    }
                } else {
                    // families
                    if ($userid > 1) { // control view privilege
                        //$qsql.= " and (profid <= 0 or hasviewprivilege($userid, profid))";
                        $qsql .= sprintf(" and (views && '%s')", \SearchDoc::getUserViewVector($userid));
                        // and get permission
                        //$qsql = str_replace("* from ", "* ,getuperm($userid,profid) as uperm from ", $qsql);
                    }
                    $qsql .= " ORDER BY $orderby LIMIT $slice OFFSET $start;";
                }
                if ($fromid != "") {
                    if ($fromid == -1) {
                    } else {
                        $fromid = abs($fromid);
                        if ($fromid > 0) {
                            \Anakeen\Core\SEManager::requireFamilyClass($fromid);
                        }
                    }
                }
            }
            if (count($tqsql) > 0) {
                if (count($tqsql) == 1) {
                    $usql = isset($tqsql[0]) ? $tqsql[0] : "";

                    // @TODO How find correct class
                    if ($fromid == -1) {
                        $docClass = \Anakeen\Core\SmartStructure::class;
                    } else {
                        if (!$fromid) {
                            $docClass = \Anakeen\Core\Internal\SmartElement::class;
                        } else {
                            $docClass = "\\Doc$fromid";
                        }
                    }

                    $query = new \Anakeen\Core\Internal\QueryDb($dbaccess, $docClass);
                } else {
                    $usql = '(' . implode($tqsql, ") union (") . ')';
                    if ($orderby) {
                        $usql .= " ORDER BY $orderby LIMIT $slice OFFSET $start;";
                    } else {
                        $usql .= " LIMIT $slice OFFSET $start;";
                    }
                    $query = new \Anakeen\Core\Internal\QueryDb($dbaccess, \Anakeen\Core\Internal\SmartElement::class);
                }
                if ($returnSqlOnly) {
                    /*
                     * Strip any "ORDER BY ..." trailing part and any trailing semi-colon
                    */
                    $usql = preg_replace('/\s+ORDER\s+BY\s+.*?$/i', '', $usql);
                    $usql = preg_replace('/;+\s*$/', '', $usql);
                    return $usql;
                }
                $mb = microtime(true);
                $tableq = $query->Query(0, 0, $qtype, $usql);

                if ($query->nb > 0) {
                    if ($qtype == "ITEM") {
                        $tretdocs[] = $tableq;
                    } else {
                        $tretdocs = array_merge($tretdocs, $tableq);
                    }
                }

                if ($query->basic_elem->msg_err != "") {
                    LogManager::notice($query->basic_elem->msg_err);
                    LogManager::notice(print_r(array(
                        "query" => $query->LastQuery,
                        "err" => $query->basic_elem->msg_err
                    ), true));
                    // print_r2(array_pop(debug_backtrace()));
                }
                if ($debug !== null) {
                    $debug["count"] = $query->nb;
                    $debug["query"] = $query->LastQuery;
                    $debug["error"] = $query->basic_elem->msg_err;
                    $debug["delay"] = sprintf("%.03fs", (microtime(true) - $mb));
                    if (!empty($debug["log"])) {
                        LogManager::notice($query->basic_elem->msg_err);
                        LogManager::notice(print_r($debug, true));
                    }
                } elseif ($query->basic_elem->msg_err != "") {
                    $debug["query"] = $query->LastQuery;
                    $debug["error"] = $query->basic_elem->msg_err;
                    LogManager::notice(print_r($debug, true));
                }
            } else {
                if ($returnSqlOnly) {
                    return "";
                }
            }
        } else {
            if ($returnSqlOnly) {
                return "";
            }
        }

        reset($tretdocs);

        return ($tretdocs);
    }

    /**
     * optimization for getChildDoc
     * @param int  $limit       if -1 no limit
     * @param bool $reallylimit if false don't return false if limit is reached
     * @return array|bool
     */
    protected static function getFldDoc($dbaccess, $dirid, $sqlfilters = array(), $limit = 100, $reallylimit = true)
    {
        if (is_array($dirid)) {
            $sqlfld = \Anakeen\Core\DbManager::getSqlOrCond($dirid, "dirid", true);
        } else {
            $sqlfld = "fld.dirid=$dirid";
        }

        $q = new \Anakeen\Core\Internal\QueryDb($dbaccess, \QueryDir::class);
        $q->AddQuery($sqlfld);
        $q->AddQuery("qtype='S'");

        if ($limit > 0) {
            $tfld = $q->Query(0, $limit + 1, "TABLE");
            // use always this mode because is more quickly
            if (($reallylimit) && ($q->nb > $limit)) {
                return false;
            }
        } else {
            $tfld = $q->Query(0, $limit + 1, "TABLE");
        }
        $t = array();
        if ($q->nb > 0) {
            foreach ($tfld as $k => $v) {
                $t[$v["childid"]] = getLatestTDoc($dbaccess, $v["childid"], $sqlfilters, ($v["doctype"] == "C") ? -1 : $v["fromid"]);

                if ($t[$v["childid"]] == false) {
                    unset($t[$v["childid"]]);
                } else {
                    if ((\Anakeen\Core\ContextManager::getCurrentUser()->id != 1) && ($t[$v["childid"]]["uperm"] & (1 << \Anakeen\Core\Internal\DocumentAccess::POS_VIEW)) == 0) { // control view
                        unset($t[$v["childid"]]);
                    }
                }
            }
        }
        uasort($t, function ($td1, $td2) {
            return strcasecmp($td1["title"], $td2["title"]);
        });

        return $t;
    }


    /**
     * query to find child directories (no recursive - only in the specified folder)
     * @param string $dbaccess database specification
     * @param int    $dirid    the id of folder where search subfolders
     * @return array
     */
    protected static function getChildDirId($dbaccess, $dirid)
    {
        $tableid = array();

        $tdir = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection($dbaccess, $dirid, "0", "ALL", array(), $userid = 1, "TABLE", 2);

        foreach ($tdir as $k => $v) {
            $tableid[] = $v["id"];
        }

        return ($tableid);
    }

// --------------------------------------------------------------------

    /**
     * return array of subfolder id until sublevel 2 (RECURSIVE)
     *
     * @param string $dbaccess database specification
     * @param int    $dirid    the id of folder where search subfolders
     * @param array  $rchilds  use for recursion (dont't set anything)
     * @param int    $level    use for recursion (dont't set anything)
     * @param int    $levelmax max recursion level (default 2)
     * @return array/int
     */
    public static function getRChildDirId($dbaccess, $dirid, $rchilds = array(), $level = 0, $levelmax = 2)
    {
        if ($level > $levelmax) {
            // $action->addWarningMsg("getRChildDirId::Max dir deep [$level levels] reached");
            return ($rchilds);
        }

        $rchilds[] = $dirid;

        $childs = self::getChildDirId($dbaccess, $dirid);

        if (count($childs) > 0) {
            foreach ($childs as $k => $v) {
                if (!in_array($v, $rchilds)) {
                    $t = array_merge($rchilds, self::getRChildDirId($dbaccess, $v, $rchilds, $level + 1, $levelmax));
                    if (is_array($t)) {
                        $rchilds = array_values(array_unique($t));
                    }
                }
            }
        }
        return ($rchilds);
    }


    /**
     * return families with the same usefor
     * @param string $dbaccess database specification
     * @param int    $userid   identifier of the user
     * @param int    $classid  the reference family to find by usefor (if 0 all families) can be an array of id
     * @param string $qtype    [TABLE|LIST] use TABLE if you can because LIST cost too many memory
     * @return array the families
     */
    public static function getClassesDoc($dbaccess, $userid, $classid = 0, $qtype = "LIST", $extraFilters = array())
    {
        $query = new \Anakeen\Core\Internal\QueryDb($dbaccess, \Anakeen\Core\SmartStructure::class);

        $query->AddQuery("doctype='C'");

        if (is_array($classid)) {
            $use = array();
            foreach ($classid as $fid) {
                $tcdoc = SEManager::getRawDocument($fid);
                $use[] = $tcdoc["usefor"];
            }
            $query->AddQuery(DbManager::getSqlOrCond($use, "usefor"));
        } elseif ($classid > 0) {
            $cdoc = new \Anakeen\Core\SmartStructure($dbaccess, $classid);
            $query->AddQuery("usefor = '" . $cdoc->usefor . "'");
        }
        // if ($userid > 1) $query->AddQuery("hasviewprivilege(" . $userid . ",docfam.profid)");
        if ($userid > 1) {
            $query->AddQuery(sprintf("views && '%s'", \SearchDoc::getUserViewVector($userid)));
        }
        if (is_array($extraFilters) && count($extraFilters) > 0) {
            foreach ($extraFilters as $filter) {
                $query->AddQuery($filter);
            }
        }
        if ($qtype == "TABLE") {
            $t = $query->Query(0, 0, $qtype);
            foreach ($t as $k => $v) {
                $t[$k]["title"] = ucfirst(getFamTitle($v));
            }
            usort($t, function ($a, $b) {
                return strcasecmp(\Anakeen\Core\Utils\Strings::Unaccent($a["title"]), \Anakeen\Core\Utils\Strings::Unaccent($b["title"]));
            });
            return $t;
        } else {
            $query->order_by = "lower(title)";
            return $query->Query(0, 0, $qtype);
        }
    }


    /**
     * return true for optimization select
     * @param string $dbaccess database specification
     * @param int    $id       identifier of the document family
     *
     * @return int false if error occured
     */
    public static function familyNeedDocread($dbaccess, $id)
    {
        if (!is_numeric($id)) {
            $id = \Anakeen\Core\SEManager::getFamilyIdFromName($id);
        }
        $id = abs(intval($id));
        if ($id == 0) {
            return false;
        }
        $dbid = \Anakeen\Core\DbManager::getDbId();
        $result = pg_query($dbid, "select id from docfam where id=$id and usedocread=1");
        if (pg_numrows($result) > 0) {
            $result = pg_query($dbid, "select fromid from docfam where fromid=$id;");
            if (pg_numrows($result) > 0) {
                return true;
            }
        }

        return false;
    }
}
