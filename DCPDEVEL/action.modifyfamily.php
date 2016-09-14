<?php
require_once ("FDL/Class.Doc.php");
function modifyfamily(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Modify some family configuration elements");
    $famid = $usage->addRequiredParameter("famid", "Family identifier");
    $docid = $usage->addRequiredParameter("docid", "Parameter identifier");
    $type = $usage->addRequiredParameter("type", "Data type", array(
        "famProfile",
        "docProfile",
        "docCv",
        "docWid"
    ));
    $usage->verify();
    /**
     * @var Docfam $family
     */
    $family = new_Doc("", $famid);
    
    if (!$family->isAffected()) {
        $err = sprintf("Undefined Document \"%s\"", $famid);
    } else {
        if (!is_a($family, "\\DocFam")) {
            $err = sprintf(___("Not a family", "dcpdevel"));
        } else {
            $err = $family->control("modifyacl");
        }
    }
    
    if (!$err) {
        switch ($type) {
            case "famProfile":
                if ($docid) {
                    $profil = new_Doc("", $docid);
                    if (!$family->isAlive()) {
                        $err = sprintf("Undefined Profil \"%s\"", $docid);
                    }
                    $profid = $profil->id;
                } else {
                    $profid = 0;
                }
                if (($family->profid == $family->id) && ($profid === 0)) {
                    // unset control
                    $family->unsetControl();
                }
                if ($family->profid != $profid) {
                    $family->addHistoryEntry(sprintf(_("Change profil to %s [%d]") , $family->getTitle($profid) , $profid));
                }
                // specific control
                $family->setProfil($profid); // change profile
                if ($family->profid == $family->id) {
                    $family->setControl();
                    $family->setProfil($family->id); // force recompute of view of specific profil itself
                    
                }
                
                $family->disableEditControl(); // need because new profil is not enable yet
                break;

            case "docProfile":
                
                if ($docid) {
                    $profil = new_Doc("", $docid);
                    if (!$family->isAlive()) {
                        $err = sprintf("Undefined Profil \"%s\"", $docid);
                    }
                    $profid = $profil->id;
                } else {
                    $profid = 0;
                }
                // change creation profile
                if (!$err && $family->cprofid != $profid) {
                    $family->addHistoryEntry(sprintf(_("Change creation profil to %s [%d]") , $family->getTitle($profid) , $profid));
                    $family->cprofid = $profid; // new creation profile access
                    $err = $family->modify();
                }
                
                break;

            case "docCv":
                if ($docid) {
                    $profil = new_Doc("", $docid);
                    if (!$family->isAlive()) {
                        $err = sprintf("Undefined Profil \"%s\"", $docid);
                    }
                    $cvid = $profil->id;
                } else {
                    $cvid = 0;
                }
                
                if (!$err && $family->ccvid != $cvid) {
                    $family->ccvid = $cvid; //  default control view for creation
                    $family->addHistoryEntry(sprintf(_("Change creation view control to %s [%d]") , $family->getTitle($cvid) , $cvid));
                    $err = $family->modify();
                }
                break;

            case "docWid":
                if ($docid) {
                    $profil = new_Doc("", $docid);
                    if (!$family->isAlive()) {
                        $err = sprintf("Undefined Workflow \"%s\"", $docid);
                    }
                    $wid = $profil->id;
                } else {
                    $wid = 0;
                }
                
                if (!$err && $family->wid != $wid) {
                    $family->wid = $wid; //  default control view for creation
                    $family->addHistoryEntry(sprintf(___("Change default workflow to %s [%d]", "dcpdevel") , $family->getTitle($wid) , $wid));
                    $err = $family->modify();
                }
                break;
        }
        
        $data[] = ["id" => 0, "label" => "No profile", "value" => "0"];
        $data[] = ["id" => $family->id, "label" => "Private", "value" => $family->id];
    }
    header('Content-Type: application/json');
    
    if ($err) {
        header("HTTP/1.0 400 Error");
        $response = ["success" => false, "error" => $err, "family" => $family->name];
    } else {
        $response = ["success" => true, "family" => $family->name];
    }
    $action->lay->noparse = true;
    $action->lay->template = json_encode($response);
}
