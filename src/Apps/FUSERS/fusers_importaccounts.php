<?php
/*
 * @author Anakeen
 * @package FDL
*/

function fusers_importaccounts(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Export accounts definition");
    
    $file = $usage->addOptionalParameter("accountImportFile", "export xml file", function ($values, $argName, ApiUsage $apiusage)
    {
        
        if ($values === ApiUsage::GET_USAGE) return "";
        if (empty($values["tmp_name"])) {
            return '';
        }
        
        if (empty($values["name"]) || empty($values["tmp_name"])) {
            $apiusage->exitError(sprintf("Error: Empty file for argument 'file'"));
        } elseif (!empty($values["error"])) {
            $apiusage->exitError(sprintf("Error in file :: %s", $values["error"]));
        } elseif ($values["type"] !== "text/xml") {
            $apiusage->exitError(sprintf("Error file is not an xml file : \"%s\"", $values["type"]));
        }
        return '';
    });
    
    $statusKey = $usage->addOptionalParameter("statusKey", "key for see progress");
    
    $stopOnError = $usage->addOptionalParameter("stopOnError", "export xml schema also", array(
        "true",
        "false"
    ) , "false") === "true";
    
    $dryRun = $usage->addOptionalParameter("analyzeOnly", "analyze only file", array(
        "true",
        "false"
    ) , "false") === "true";
    
    $usage->verify();

    setMaxExecutionTimeTo(-1); // May be long
    if (empty($file["tmp_name"])) {
        $action->lay->set("EMPTY", true);
    } else {
        // Memo user preferences
        $uprefs=json_decode($action->getParam("FUSERS_PORTPREF", "{}"), true);
        $uprefs["import"]=array(
            "stopOnError"=>$stopOnError,
            "dryRun"=>$dryRun
        );
        $action->setParamU("FUSERS_PORTPREF", json_encode($uprefs));

        $errorCount = 0;
        $import = new \Dcp\Core\ImportAccounts();
        if ($statusKey) {
            $import->setSessionKey("IA::" . $statusKey);
        }
        $import->setFile($file["tmp_name"]);
        $import->setAnalyzeOnly($dryRun);
        $import->setStopOnError($stopOnError);
        try {
            $import->import();
        }
        catch(\Dcp\Core\Exception $e) {
            print_r($e->getDcpMessage());
        }
        $report = $import->getReport();
        $logins = array();
        
        foreach ($report as & $row) {
            $row["classError"] = $row["error"] ? "error" : "";
            if ($row["error"]) {
                $errorCount++;
            }
            $logins[$row["login"]] = true;
        }
        
        $action->lay->eSetBlockData("REPORT", $report);
        $action->lay->set("EMPTY", false);
        $action->lay->set("ERRORS", $errorCount);
        $action->lay->set("DRYRUN", $dryRun);
        if ($errorCount) {
            $action->lay->set("reportMessage", sprintf(n___("%d error detected", "%d errors detected", $errorCount, "fusers_import") , $errorCount));
        } else {
            $action->lay->set("reportMessage", sprintf(___("no errors detected", "fusers_import")));
        }
        $action->lay->set("countMessage", sprintf(n___("%d account processed", "%d accounts processed", count($logins) , "fusers_import") , count($logins)));
    }
}
