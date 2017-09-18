<?php
/*
 * @author Anakeen
 * @package FDL
*/

function fusers_importstatus(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Get form option to import accounts");
    
    $statusKey = $usage->addRequiredParameter("statusKey", "key for see progress");
    $abort = $usage->addOptionalParameter("abort", "Abord current import", array(
        "true",
        "false"
    ) , "false") === "true";
    $usage->verify();
    $import = new \Dcp\Core\ImportAccounts();
    
    $import->setSessionKey("IA::" . $statusKey);
    
    if ($abort) {
        $import->abortSession();
    }
    $status = array(
        "msg" => $import->getSessionMessage()
    );
    $action->lay->template = json_encode($status);
    $action->lay->noparse = true;
    header('Content-type: application/json');
}
