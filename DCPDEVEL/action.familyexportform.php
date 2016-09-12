<?php
require_once ("FDL/Class.Doc.php");
function familyexportform(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Export Family Configuration Form");
    $familyId = $usage->addRequiredParameter("family", "Family identifier");
    $usage->verify();
    /**
     * @var DocFam $family
     */
    $family = new_Doc("", $familyId);
    if (!$family->isAffected()) {
        $action->exitError("Undefined family");
    }
    $action->parent->addJsRef("lib/jquery/1.7.2/jquery.js");
    $action->parent->addJsRef("lib/jquery-ui-1.12.0/jquery-ui.js");
    $action->parent->addJsRef("lib/jquery-dataTables/1.10/js/jquery.dataTables.js");
    $action->parent->addJsRef("DCPDEVEL/Layout/familyexport.js");
    
    $action->parent->addCssRef("WHAT/Layout/size-normal.css");
    $action->parent->addCssRef("lib/jquery-ui-1.12.0/jquery-ui.css");
    $action->parent->addCssRef("lib/jquery-ui-1.12.0/jquery-ui.structure.css");
    $action->parent->addCssRef("lib/jquery-ui-1.12.0/jquery-ui.theme.min.css");
    
    $action->parent->addCssRef("lib/jquery-dataTables/1.10/css/jquery.dataTables.css");
    $action->parent->addCssRef("lib/jquery-dataTables/1.10/css/dataTables.jqueryui.css");
    
    $action->parent->addCssRef("DCPDEVEL/Layout/develmain.css");
    
    $famConf = new \Dcp\Devel\ExportFamily();
    $famConf->setFamily($family);
    $documents = $famConf->getDocumentToExport();
    
    $exportDocids = [];
    foreach ($documents as $document) {
        $exportDocids[] = $document["docid"];
    }
    $documentData = [];
    $dl = new \DocumentList();
    
    $dl->addDocumentIdentifiers($exportDocids);
    foreach ($dl as $document) {
        $documentData[] = ["icon" => $document->getIcon("", 16) , "name" => $document->name, "docid" => $document->id, "label" => $document->getTitle() , "famlabel" => $document->getFamilyDocument()->getTitle() , ];
    }
    
    $action->lay->set("famid", $family->id);
    $action->lay->eSet("famTitle", $family->getTitle());
    $action->lay->eSetBlockData("documents", $documentData);
}
