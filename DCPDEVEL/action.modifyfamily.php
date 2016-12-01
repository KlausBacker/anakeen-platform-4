<?php
require_once ("FDL/Class.Doc.php");
function modifyfamily(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Modify some family configuration elements");
    $famid = $usage->addRequiredParameter("famid", "Family identifier");
    $value = $usage->addRequiredParameter("value", "Parameter identifier");
    $type = $usage->addRequiredParameter("type", "Data type", array(
        "famProfile",
        "docProfile",
        "docCv",
        "docWid",
        "newWid",
        "newCvid",
        "icon"
    ));
    $usage->setStrictMode(false);
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
                if ($value) {
                    $profil = new_Doc("", $value);
                    if (!$family->isAlive()) {
                        $err = sprintf("Undefined Profil \"%s\"", $value);
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
                    $family->addHistoryEntry(sprintf(___("Change profil to %s [%d]", "dcpdev") , $family->getTitle($profid) , $profid));
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
                
                if ($value) {
                    $profil = new_Doc("", $value);
                    if (!$family->isAlive()) {
                        $err = sprintf("Undefined Profil \"%s\"", $value);
                    }
                    $profid = $profil->id;
                } else {
                    $profid = 0;
                }
                // change creation profile
                if (!$err && $family->cprofid != $profid) {
                    $family->addHistoryEntry(sprintf(___("Change creation profil to %s [%d]", "dcpdev") , $family->getTitle($profid) , $profid));
                    $family->cprofid = $profid; // new creation profile access
                    $err = $family->modify();
                }
                
                break;

            case "docCv":
                if ($value) {
                    $profil = new_Doc("", $value);
                    if (!$family->isAlive()) {
                        $err = sprintf("Undefined Profil \"%s\"", $value);
                    }
                    $cvid = $profil->id;
                } else {
                    $cvid = 0;
                }
                
                if (!$err && $family->ccvid != $cvid) {
                    $family->ccvid = $cvid; //  default control view for creation
                    $family->addHistoryEntry(sprintf(___("Change creation view control to %s [%d]", "dcpdev") , $family->getTitle($cvid) , $cvid));
                    $err = $family->modify();
                }
                break;

            case "newCvid":
                // Create new control view
                if ($value) {
                    $newWid = createDoc("", $value);
                    if ($newWid) {
                        $err = $newWid->setValue(\Dcp\AttributeIdentifiers\Cvdoc::cv_famid, $family->id);
                        $err.= $newWid->setValue(\Dcp\AttributeIdentifiers\Cvdoc::ba_title, sprintf(___("Default workflow for %s family", "dcpdevel") , $family->getTitle()));
                        if (!$err) {
                            $err = $newWid->store();
                        }
                        if (!$err) {
                            $family->ccvid = $newWid->id; //  default control view for creation
                            $family->addHistoryEntry(sprintf(___("Change default view control to %s [%d]", "dcpdevel") , $family->getTitle($newWid->id) , $newWid->id));
                            $err = $family->modify();
                        }
                    }
                }
                break;

            case "newWid":
                // Create new workflow
                if ($value) {
                    $newWid = createDoc("", $value);
                    if ($newWid) {
                        $err = $newWid->setValue(\Dcp\AttributeIdentifiers\Wdoc::wf_famid, $family->id);
                        $err.= $newWid->setValue(\Dcp\AttributeIdentifiers\Wdoc::ba_title, sprintf(___("Default workflow for %s family", "dcpdevel") , $family->getTitle()));
                        if (!$err) {
                            $err = $newWid->store();
                        }
                        if (!$err) {
                            $family->wid = $newWid->id; //  default workflow
                            $family->addHistoryEntry(sprintf(___("Change default workflow to %s [%d]", "dcpdevel") , $family->getTitle($newWid->id) , $newWid->id));
                            $err = $family->modify();
                        }
                    }
                }
                break;

            case "docWid":
                if ($value) {
                    $wdoc = new_Doc("", $value);
                    if (!$family->isAlive()) {
                        $err = sprintf("Undefined Workflow \"%s\"", $value);
                    }
                    $wid = $wdoc->id;
                } else {
                    $wid = 0;
                }
                
                if (!$err && $family->wid != $wid) {
                    $family->wid = $wid; //  default control view for creation
                    $family->addHistoryEntry(sprintf(___("Change default workflow to %s [%d]", "dcpdevel") , $family->getTitle($wid) , $wid));
                    $err = $family->modify();
                }
                break;

            case "icon":
                if (!is_uploaded_file($value['tmp_name'])) {
                    $err = (___("file not expected : possible attack : update aborted", "dcpdev"));
                }
                
                $imageSize = getimagesize($value['tmp_name']);
                if (!$imageSize) {
                    $err = "File is not recognized like an image";
                }
                
                if (!$err) {
                    $vid = \Dcp\VaultManager::storeFile($value['tmp_name'], $value['name'], true);
                    $fileVaultInfo = \Dcp\VaultManager::getFileInfo($vid);
                    
                    $family->changeIcon(sprintf("%s|%s|%s", $fileVaultInfo->mime_s, $fileVaultInfo->id_file, $fileVaultInfo->name));
                }
                break;
        }
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
