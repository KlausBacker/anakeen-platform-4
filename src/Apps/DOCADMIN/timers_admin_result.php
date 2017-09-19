<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Timer management
 *
 * @author Anakeen
 * @version $Id: timers_admin_result.php,v 1.4 2009/01/07 18:04:19 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocTimer.php");
include_once ("FDL/viewtimers.php");
/**
 * Timers management
 * @param Action &$action current action
 * @global string $type Http var : request type
 */
function timers_admin_result(Action & $action)
{
    
    $type = GetHttpVars("type", "next");
    $offset = GetHttpVars("offset", 0);
    $limit = GetHttpVars("limit", 100);
    $filter = GetHttpVars("filter");
    $purgeday = intval(GetHttpVars("purgeday", 7));
    $dbaccess = $action->dbaccess;
    
    if ($type == "purge") {
        $type = "previous";
        
        $q = new QueryDb($dbaccess, "DocTimer");
        $delfilter = '';
        if ($filter) $delfilter = sprintf("and title ~* '%s'", pg_escape_string($filter));
        $sqlpurge = "delete from doctimer where donedate is not null and donedate < now() - interval '$purgeday day' $delfilter;";
        
        $timerhourlimit = max($purgeday, $action->getParam("FDL_TIMERHOURLIMIT", 2));
        $q->Query(0, 0, 'TABLE', $sqlpurge);
        $sqlpurge = "delete from doctimer where tododate is not null and tododate < now() - interval '$timerhourlimit day' $delfilter;";
        
        $q->Query(0, 0, 'TABLE', $sqlpurge);
    }
    if ($type == "detach") {
        $type = "next";
        
        $q = new QueryDb($dbaccess, "DocTimer");
        $delfilter = '';
        if ($filter) $delfilter = sprintf("and title ~* '%s'", pg_escape_string($filter));
        $sqlpurge = "delete from doctimer where donedate is null $delfilter;";
        
        $q->Query(0, 0, 'TABLE', $sqlpurge);
    }
    
    $q = new QueryDb($dbaccess, "DocTimer");
    if ($type == "next") {
        $q->addQuery("tododate is not null");
        $timerhourlimit = getParam("FDL_TIMERHOURLIMIT", 2);
        $q->addQuery("tododate > now() - interval '$timerhourlimit hour'");
        $q->order_by = 'tododate';
    }
    if ($type == "skip") {
        $q->addQuery("tododate is not null");
        $q->addQuery("tododate != 'infinity'");
        $timerhourlimit = getParam("FDL_TIMERHOURLIMIT", 2);
        $q->addQuery("tododate < now() - interval '$timerhourlimit hour'");
        $q->order_by = 'tododate';
    }
    if ($type == "previous") {
        $q->addQuery("donedate is not null");
        $q->order_by = 'donedate desc';
    }
    if ($filter) $q->addQuery(sprintf("title ~* '%s'", pg_escape_string($filter)));
    $t = $q->Query($offset, $limit, "TABLE");
    if (is_array($t)) {
        foreach ($t as $k => $v) {
            $t[$k]["hact"] = humanactions($v["actions"], $dbaccess);
            $t[$k]["hdelay"] = computehumandelay($v["tododate"]);
            if ($v["tododate"] === 'infinity') {
                 $t[$k]["tododate"] = '';
            }
        }
    }
    $action->lay->setBlockData("TIMERS", $t);
    $action->lay->set("isprev", ($type == "previous"));
    $action->lay->set("isnext", ($type == "next") || ($type == "skip"));
}
function computehumandelay($tdate)
{
    if (!$tdate) return '';
    if ($tdate === 'infinity') {
        return ___("No date setting","docadmin");
    }
    $jdnow = StringDateToJD(Doc::getTimeDate());
    $jdtdate = StringDateToJD($tdate);
    if (($jdtdate - $jdnow) < 0) $hd = "- ";
    else $hd = "";
    return $hd . humandelay(abs($jdtdate - $jdnow));
}

function humanactions($act, $dbaccess)
{
    $oact = unserialize($act);
    if (!$oact) return "-";
    $tmsg = array();
    foreach ($oact as $k => $v) {
        if ($v) {
            switch ($k) {
                case "tmail":
                    $tva = Doc::rawValueToArray(str_replace('<BR>', "\n", $v));
                    foreach ($tva as $idmail) {
                        $tm = new_doc($dbaccess, $idmail);
                        if ($tm->isAlive()) {
                            $msg = sprintf(_("timer:send mail with template %s [%d]") , $tm->title, $tm->id);
                            $tmsg[] = $msg;
                        }
                    }
                    break;

                case "state":
                    $msg = sprintf(_("timer:change state to %s") , _($v));
                    $tmsg[] = $msg;
                    break;

                case "method":
                    $msg = sprintf(_("timer:apply method %s") , $v);
                    $tmsg[] = $msg;
                    break;
            }
        }
    }
    return implode($tmsg, ".\n");
}
?>