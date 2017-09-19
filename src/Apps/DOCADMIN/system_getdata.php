<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Timer management
 *
 * @author Anakeen
 * @version $Id: admin_timers.php,v 1.2 2009/01/02 17:43:50 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocTimer.php");
/**
 * Timers management
 * @param Action &$action current action
 */
function system_getdata(&$action)
{
    
    $usage = new ActionUsage($action);
    $usage->setText("Get data for system_doc action");
    $searchId = $usage->addOptionalParameter("id", "search identifier");
    $familyId = $usage->addOptionalParameter("famid", "family filter identifier");
    $usage->setStrictMode(false);
    $usage->verify();
    
    $data = array();
    
    $d = new_doc($action->dbaccess, $searchId);
    if ($familyId && $familyId[0] == '-') {
        $onlyFam = true;
        $familyId = substr($familyId, 1);
    } else {
        $onlyFam = false;
    }
    
    $fam = new_doc($action->dbaccess, $familyId);
    if ($d->isAffected() || $fam->isAffected()) {
        
        $iDisplayStart = $action->getArgument("iDisplayStart");
        $iDisplayLength = $action->getArgument("iDisplayLength");
        $sSearch = $action->getArgument("sSearch");
        
        $s = new SearchDoc($action->dbaccess, $fam->id);
        $s->only = $onlyFam;
        if ($d->initid) {
            $s->useCollection($d->initid);
        }
        if ($sSearch) {
            $s->addFilter("title ~* '%s'", $sSearch);
        }
        if ($iDisplayStart) {
            $s->setStart($iDisplayStart);
        }
        if ($iDisplayLength) {
            $s->setSlice($iDisplayLength);
        }
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        /**
         * @var Doc $doc
         */
        foreach ($dl as $doc) {
            $data[] = array(
                // "docicon"=>sprintf('<img src="%s">',$doc->getIcon('',15)),
                "docicon" => $doc->getIcon('', 15) ,
                "docid" => $doc->id,
                "doctitle" => $doc->getHtmlTitle()
            );
        }
        
        $allCount = $s->count();
        if ($iDisplayStart || $allCount == $iDisplayLength) {
            $s->reset();
            $s->setStart(0);
            $s->setSlice("all");
            $allCount = $s->onlyCount();
        }
        $output = array(
            "sEcho" => intval($_GET['sEcho']) ,
            "iTotalRecords" => $allCount,
            "iTotalDisplayRecords" => $s->count() ,
            "aaData" => $data
        );
    } else {
        $output = array(
            "sEcho" => intval($_GET['sEcho']) ,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => $data
        );
    }
    $action->lay->noparse = true;
    $action->lay->template = json_encode($output);
}
?>