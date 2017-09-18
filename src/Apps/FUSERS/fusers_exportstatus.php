<?php
/*
 * @author Anakeen
 * @package FDL
*/

function fusers_exportstatus(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Get status when export accounts");
    
    $statusKey = $usage->addRequiredParameter("statusKey", "key for see progress");
    $abort = $usage->addOptionalParameter("abort", "Abord current import", array(
        "true",
        "false"
    ) , "false") === "true";
    $usage->verify();
    $export = new \Dcp\Core\ExportAccounts();
    
    $export->setSessionKey("EA::" . $statusKey);
    if ($abort) {
        $export->abortSession();
    }
    $status = array(
        "msg" => $export->getSessionMessage()
    );
    $action->lay->template = json_encode($status);
    $action->lay->noparse = true;
    header('Content-type: application/json');
}
