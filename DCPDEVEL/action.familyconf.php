<?php
require_once ("FDL/Class.Doc.php");
function familyconf(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $familyId = $usage->addRequiredParameter("family", "Family identifier");
    $usage->setStrictMode(false);
    $usage->verify();
    /**
     * @var DocFam $family
     */
    $family = new_Doc("", $familyId);
    if (!$family->isAffected()) {
        $action->exitError("Undefined family");
    }
    
    $action->lay->eSet("CreateLabel", sprintf(___("Create %s", "dcpdevel") , $family->getTitle()));
    i18N_familyconf($action, $family);
    
    $action->lay->set("icon", $family->getIcon("", 32));
    $action->lay->set("bigicon", $family->getIcon("", 64));
    $action->lay->eSet("famid", $family->id);
    $action->lay->eSet("famname", $family->name);
    $action->lay->eSet("profid", $family->profid);
    $action->lay->eSet("cprofid", $family->cprofid);
    $action->lay->eSet("label", $family->getTitle());
    $action->lay->eSet("idFrame", uniqid("frame"));
    $action->lay->eSet("wid", $family->wid);
    $action->lay->eSet("cvid", $family->ccvid);
    if ($family->wid) {
        $workflow = new_Doc("", $family->wid);
        $action->lay->eSet("wLabel", $workflow->getTitle());
        $action->lay->eSet("wIcon", $workflow->getIcon("", 20));
    } else {
        $action->lay->eSet("wLabel", ___("No default workflow configurated", "dcpdevel"));
    }
    
    if ($family->ccvid) {
        $cv = new_Doc("", $family->ccvid);
        $action->lay->eSet("cvLabel", $cv->getTitle());
        $action->lay->eSet("cvIcon", $cv->getIcon("", 20));
    } else {
        $action->lay->eSet("cvLabel", ___("No default view control configurated", "dcpdevel"));
    }
    if ($family->profid) {
        $profil = new_Doc("", $family->profid);
        $action->lay->eSet("pLabel", $profil->getTitle());
        $action->lay->eSet("pIcon", $profil->getIcon("", 20));
    }
    if ($family->cprofid) {
        $cprofil = new_Doc("", $family->cprofid);
        $action->lay->eSet("cpLabel", $cprofil->getTitle());
        $action->lay->eSet("cpIcon", $cprofil->getIcon("", 20));
    }
    
    $cv = new_Doc("", "CVDOC");
    $subFam = $cv->getChildFam();
    if ($subFam) {
        $cvfams[] = ["cvid" => $cv->id, "cvtitle" => $cv->getTitle() ];
        foreach ($subFam as $sub) {
            $cvfams[] = ["cvid" => $sub["id"], "cvtitle" => $cv->getTitle($sub["id"]) ];
        }
        $action->lay->eSetBlockData("cvfams", $cvfams);
        $action->lay->set("cvfam", true);
        $action->lay->set("cvCreate", ___("Initialize control", "dcpdevel"));
    } else {
        $action->lay->set("cvfam", false);
    }
    
    $sw = new SearchDoc("", "-1");
    $sw->addFilter("usefor ~ 'W' ");
    $sw->addFilter("id != 20 ");
    $sw->setObjectReturn(true);
    $fws = $sw->search()->getDocumentList();
    $tws = [];
    foreach ($fws as $fw) {
        $tws[] = ["wfid" => $fw->id, "wtitle" => $fw->getTitle() ];
    }
    $action->lay->eSetBlockData("wfams", $tws);
    $action->lay->set("wfam", count($tws) > 0);
}

function i18N_familyconf(Action & $action, \DocFam $family)
{
    $action->lay->eSet("AttributesLabel", ___("Attributes", "dcpdevel"));
    $action->lay->eSet("EnumerateLabel", ___("Enumerates", "dcpdevel"));
    $action->lay->eSet("DefaultLabel", ___("Defaut values and parameters", "dcpdevel"));
    $action->lay->eSet("List", sprintf(___("\"%s\" List", "dcpdevel") , $family->getTitle()));
    
    if ($family->profid) {
        $action->lay->eSet("ChangeFamilyProfileLabel", ___("Choose another existing family profile", "dcpdevel"));
    } else {
        $action->lay->eSet("ChangeFamilyProfileLabel", ___("Choose existing family profile", "dcpdevel"));
    }
    $action->lay->eSet("ChangeNewDocProfileLabel", ___("Choose profile and view control for new documents", "dcpdevel"));
    $action->lay->eSet("ChangeFamilyAccess", ___("Configure family access", "dcpdevel"));
    $action->lay->eSet("ChangeNewDocAccess", ___("Configure new doc access", "dcpdevel"));
    $action->lay->eSet("ChooseDefaultWorkflow", ___("Choose default workflow", "dcpdevel"));
    $action->lay->eSet("ViewDefaultWorkflow", ___("View default workflow", "dcpdevel"));
    $action->lay->eSet("WorkflowLabel", ___("Workflow", "dcpdevel"));
    $action->lay->eSet("SecurityLabel", ___("Security", "dcpdevel"));
    $action->lay->eSet("ConfigurationLabel", ___("Configuration", "dcpdevel"));
    $action->lay->eSet("ExportConfLabel", ___("Export Configuration", "dcpdevel"));
    $action->lay->eSet("NoWorkflow", ___("No default workflow configurated", "dcpdevel"));
    $action->lay->eSet("DefaultWorkflow", ___("Default workflow", "dcpdevel"));
    $action->lay->eSet("ViewDefaultCv", ___("Default view control", "dcpdevel"));
    $action->lay->eSet("PresentationLabel", ___("Back", "dcpdevel"));
    $action->lay->eSet("ViewControl", ___("View Control", "dcpdevel"));
    $action->lay->eSet("cvCreate", ___("Create new view control", "dcpdevel"));
    $action->lay->eSet("ConfigurationLabel", ___("Configuration", "dcpdevel"));
    $action->lay->eSet("FamilyProfil", ___("Family profile", "dcpdevel"));
    $action->lay->eSet("DocumentProfil", ___("Profile for new documents", "dcpdevel"));
    $action->lay->eSet("ExportConf", ___("Export configuration", "dcpdevel"));
    $action->lay->eSet("CreateNewDocProfile", ___("Create profile for new document", "dcpdevel"));
    $action->lay->eSet("InitialiseFamProfileLabel", ___("Initialize specific profile for family", "dcpdevel"));
}
