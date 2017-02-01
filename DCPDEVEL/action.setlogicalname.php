<?php
require_once("FDL/Class.Doc.php");
function setlogicalname(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Set Logical name");
    $docid = $usage->addRequiredParameter("id", "Document identifier");
    $logicalName = $usage->addRequiredParameter("name", "New name");
    $usage->setStrictMode(false);
    $usage->verify();
    /**
     * @var Doc $doc
     */
    $doc = new_Doc("", $docid);
    
    $message = "";
    $err = "";
    if (!$doc->isAffected()) {
        $err = sprintf("Undefined Document \"%s\"", $docid);
    } else {
        if (is_a($doc, "\\DocFam")) {
            $err = sprintf(___("No update logical name for family", "dcpdevel"));
        }
        
        if (!$err && $logicalName !== ":initial:") {
            if ($logicalName === ":auto:") {
                $logicalName = sprintf("%s-%s", $doc->fromname, str_replace(" ", "_", unaccent($doc->getTitle())));
            }
            
            $oldName = $doc->name;
            if ($logicalName === ":clear:") {
                simpleQuery("", sprintf("update doc set name=null where initid=%d", $doc->initid));
                simpleQuery("", sprintf("delete from docname where id=%d", $doc->id));
                $message = sprintf(___("Clear logical name \"%s\"", "dcpdevel"), $oldName);
                $doc->name = null;
            } else {
                $err = $doc->setLogicalName($logicalName, true);
                if ($err == "") {
                    if ($oldName) {
                        $message = sprintf(___("Update logical name from \"%s\" to \"%s\"", "dcpdevel"), $oldName, $doc->name);
                    } else {
                        $message = sprintf(___("Set logical name to \"%s\"", "dcpdevel"), $doc->name);
                    }
                    $doc->addHistoryEntry($message);
                }
            }
        }
    }
    header('Content-Type: application/json');
    
    if ($err) {
        header("HTTP/1.0 400 Error");
        $response = ["success" => false, "error" => $err, "logicalName" => $doc->name];
    } else {
        $response = ["success" => true, "message" => $message, "logicalName" => $doc->name];
    }
    $action->lay->noparse = true;
    $action->lay->template = json_encode($response);
}
