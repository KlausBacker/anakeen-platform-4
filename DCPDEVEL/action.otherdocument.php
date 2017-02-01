<?php
require_once("FDL/Class.Doc.php");
function otherdocument(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Add document to family export");
    $docid = $usage->addRequiredParameter("docid", "Document identifier");
    $famid = $usage->addRequiredParameter("famid", "Family identifier");
    $method = $usage->addRequiredParameter("method", "Add or delete", array(
        "ADD",
        "DELETE"
    ));
    $usage->setStrictMode(false);
    $usage->verify();
    /**
     * @var Doc $doc
     */
    $doc = new_Doc("", $docid);
    
    $tag = '';
    /**
     * @var Docfam $fam
     */
    $fam = new_Doc("", $famid);
    $err = '';
    $message = '';
    if (!$doc->isAffected()) {
        $err = sprintf("Undefined Document \"%s\"", $docid);
    }
    
    if (!$err && !$fam->isAffected()) {
        $err = sprintf("Undefined Family \"%s\"", $docid);
    }
    if (!$err && !is_a($fam, "\\DocFam")) {
        $err = sprintf("Not a family \"%s\"", $docid);
    }
    if (!$err) {
        $tag = sprintf("%s%s", Dcp\Devel\ExportFamily::OTHERTAGPREFIX, $fam->name);
        switch ($method) {
            case "ADD":
                $doc->addATag($tag);
                break;

            case "DELETE":
                $doc->delATag($tag);
                break;
        }
    }
    
    header('Content-Type: application/json');
    
    if ($err) {
        header("HTTP/1.0 400 Error");
        $response = ["success" => false, "error" => $err];
    } else {
        $response = ["success" => true, "message" => $message, "tag" => $tag, "family" => ["title" => $doc->getFamilyDocument()->getTitle() ], "document" => ["title" => $doc->getTitle() , "id" => $doc->initid, "name" => $doc->name, "icon" => $doc->getIcon("", 16) ]];
    }
    $action->lay->noparse = true;
    $action->lay->template = json_encode($response);
}
