<?php
require_once ("FDL/Class.Doc.php");
function searchsysdoc(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Get sys doc list");
    $famid = $usage->addRequiredParameter("famid", "Family identifier");
    $type = $usage->addRequiredParameter("type", "Data type", array(
        "famProfile",
        "docProfile",
        "docCv",
        "docWid"
    ));
    $term = $usage->addOptionalParameter("term", "Filter term");
    $usage->verify();
    /**
     * @var Docfam $doc
     */
    $doc = new_Doc("", $famid);
    
    $message = "";
    $err = "";
    if (!$doc->isAffected()) {
        $err = sprintf("Undefined Document \"%s\"", $famid);
    } else {
        if (!is_a($doc, "\\DocFam")) {
            $err = sprintf(___("Not a family", "dcpdevel"));
        }
        
        switch ($type) {
            case "famProfile":
                $s = new SearchDoc("", "PFAM");
                if ($term) {
                    $s->addFilter("title ~* '%s' or name ~* '%s'", $term, $term);
                }
                $docData = $s->search();
                foreach ($docData as $rawDoc) {
                    
                    $data[] = ["id" => $rawDoc["id"], "label" => sprintf("%s (%s)", $rawDoc["title"], $rawDoc["name"]) , "value" => $rawDoc["id"]];
                }
                $data[] = ["id" => 0, "label" => ___("No profile (free access)", "dcpdevel") , "value" => "0"];
                $data[] = ["id" => $doc->id, "label" => ___("Specific profil", "dcpdevel") , "value" => $doc->id];
                break;

            case "docProfile":
                $tdoc = createDoc("", $doc->id);
                $s = new SearchDoc("", $tdoc->defProfFamId);
                $s->only = true;
                $parentIds = $doc->getFromDoc();
                
                $s->addFilter("dpdoc_famid is null or " . $s->sqlcond($parentIds, "dpdoc_famid", false));
                if ($term) {
                    $s->addFilter("title ~* '%s' or name ~* '%s'", $term, $term);
                }
                $docData = $s->search();
                foreach ($docData as $rawDoc) {
                    
                    $title = $rawDoc["title"];
                    if ($rawDoc["name"]) {
                        $title.= ' (' . $rawDoc["name"] . ')';
                    }
                    $data[] = ["id" => $rawDoc["id"], "label" => $title, "value" => $rawDoc["id"]];
                }
                $data[] = ["id" => 0, "label" => ___("No profile (free access)", "dcpdevel") , "value" => "0"];
                break;

            case "docCv":
                $s = new SearchDoc("", "CVDOC");
                
                $parentIds = $doc->getFromDoc();
                
                $s->addFilter("cv_famid is null or " . $s->sqlcond($parentIds, "cv_famid", false));
                if ($term) {
                    $s->addFilter("title ~* '%s' or name ~* '%s'", $term, $term);
                }
                $docData = $s->search();
                foreach ($docData as $rawDoc) {
                    
                    $title = $rawDoc["title"];
                    if ($rawDoc["name"]) {
                        $title.= ' (' . $rawDoc["name"] . ')';
                    }
                    $data[] = ["id" => $rawDoc["id"], "label" => $title, "value" => $rawDoc["id"]];
                }
                $data[] = ["id" => 0, "label" => ___("No cv", "dcpdevel") , "value" => "0"];
                break;

            case "docWid":
                $s = new SearchDoc("", "WDOC");
                
                $parentIds = $doc->getFromDoc();
                $s->addFilter("wf_famid is null or " . $s->sqlcond($parentIds, "wf_famid", false));
                if ($term) {
                    $s->addFilter("title ~* '%s' or name ~* '%s'", $term, $term);
                }
                $docData = $s->search();
                foreach ($docData as $rawDoc) {
                    
                    $title = $rawDoc["title"];
                    if ($rawDoc["name"]) {
                        $title.= ' (' . $rawDoc["name"] . ')';
                    }
                    $data[] = ["id" => $rawDoc["id"], "label" => $title, "value" => $rawDoc["id"]];
                }
                $data[] = ["id" => 0, "label" => ___("No workflow", "dcpdevel") , "value" => "0"];
                break;
        }
    }
    
    header('Content-Type: application/json');
    
    if ($err) {
        header("HTTP/1.0 400 Error");
        $response = ["success" => false, "error" => $err, "family" => $doc->name];
    } else {
        $response = $data;
    }
    $action->lay->noparse = true;
    $action->lay->template = json_encode($response);
}
