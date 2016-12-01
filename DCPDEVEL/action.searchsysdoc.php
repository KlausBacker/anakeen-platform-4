<?php
require_once ("FDL/Class.Doc.php");
function searchsysdoc(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Get sys doc list");
    $famid = $usage->addRequiredParameter("famid", "Family identifier");
    $type = $usage->addRequiredParameter("type", "Data type", array(
        "documents",
        "families",
        "famProfile",
        "docProfile",
        "docCv",
        "docWid"
    ));
    $term = $usage->addOptionalParameter("term", "Filter term");
    $usage->setStrictMode(false);
    $usage->verify();
    /**
     * @var Docfam $family
     */
    $family = new_Doc("", $famid);
    $data = [];
    $err = "";
    if (!$family->isAffected()) {
        $err = sprintf("Undefined Document \"%s\"", $famid);
    } else {
        if (!is_a($family, "\\DocFam")) {
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
                $data[] = ["id" => $family->id, "label" => ___("Specific profil", "dcpdevel") , "value" => $family->id];
                break;

            case "docProfile":
                $tdoc = createDoc("", $family->id);
                $s = new SearchDoc("", $tdoc->defProfFamId);
                $s->only = true;
                $parentIds = $family->getFromDoc();
                
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
                
                $parentIds = $family->getFromDoc();
                
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
                
                $parentIds = $family->getFromDoc();
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

            case "families":
                $s = new SearchDoc("", -1);
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
                if (count($data) === 0) {
                    $data[] = ["id" => 0, "label" => sprintf(___("No families match \"%s\"", "dcpdevel") , $term) , "value" => "0"];
                }
                break;

            case "documents":
                $s = new SearchDoc("", $family->id);
                $s->setSlice(50);
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
                if (count($data) === 0) {
                    $data[] = ["id" => 0, "label" => sprintf(___("No \"%s\" match \"%s\"", "dcpdevel") , $family->getTitle() , $term) , "value" => "0"];
                }
                break;
        }
    }
    
    header('Content-Type: application/json');
    
    if ($err) {
        header("HTTP/1.0 400 Error");
        $response = ["success" => false, "error" => $err, "family" => $family->name];
    } else {
        $response = $data;
    }
    $action->lay->noparse = true;
    $action->lay->template = json_encode($response);
}
