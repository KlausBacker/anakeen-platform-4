<?php
require_once ("FDL/Class.Doc.php");
function familyexportform(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Export Family Configuration Form");
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
    $action->parent->addJsRef("lib/jquery/1.7.2/jquery.js");
    $action->parent->addJsRef("lib/jquery-ui-1.12.0/jquery-ui.js");
    $action->parent->addJsRef("lib/jquery-dataTables/1.10/js/jquery.dataTables.js");
    $action->parent->addJsRef("DCPDEVEL/Layout/combobox.js");
    $action->parent->addJsRef("DCPDEVEL/Layout/familyexport.js");
    
    $action->parent->addCssRef("WHAT/Layout/size-normal.css");
    $action->parent->addCssRef("lib/jquery-ui-1.12.0/jquery-ui.css");
    $action->parent->addCssRef("lib/jquery-ui-1.12.0/jquery-ui.structure.css");
    $action->parent->addCssRef("lib/jquery-ui-1.12.0/jquery-ui.theme.min.css");
    
    $action->parent->addCssRef("lib/jquery-dataTables/1.10/css/jquery.dataTables.css");
    $action->parent->addCssRef("lib/jquery-dataTables/1.10/css/dataTables.jqueryui.css");
    // $action->parent->addCssRef("DCPDEVEL/Layout/develmain.css");
    $action->parent->addCssRef("DCPDEVEL/Layout/familyexportform.css");
    
    $famConf = new \Dcp\Devel\ExportFamily();
    $famConf->setFamily($family);
    $documents = $famConf->getDocumentToExport();
    $others = $famConf->getOtherDocumentToExport();
    
    $exportDocids = $exportOtherDocids = [];
    foreach ($documents as $document) {
        $exportDocids[] = $document["docid"];
    }
    foreach ($others as $document) {
        $exportOtherDocids[] = $document["docid"];
    }
    $documentData = $otherData = [];
    $dl = new \DocumentList();
    
    $dl->addDocumentIdentifiers(array_merge($exportDocids, $exportOtherDocids));
    foreach ($dl as $document) {
        
        $data = ["TPL" => false, "icon" => $document->getIcon("", 16) , "name" => $document->name, "docid" => $document->id, "label" => $document->getTitle() , "famlabel" => $document->getFamilyDocument()->getTitle() ];
        if (in_array($document->id, $exportDocids)) {
            $documentData[] = $data;
        } else {
            $otherData[] = $data;
        }
    }
    $otherData[] = ["TPL" => true, "icon" => '{{icon}}', "name" => '{{name}}', "docid" => '{{docid}}', "label" => '{{label}}', "famlabel" => '{{famLabel}}'];
    
    $action->lay->set("famid", $family->id);
    $action->lay->eSet("famTitle", $family->getTitle());
    $action->lay->eSetBlockData("documents", $documentData);
    $action->lay->eSetBlockData("othersdocuments", $otherData);
    $action->lay->set("csvcomma", $action->getParam("CSV_SEPARATOR") === ",");
    $action->lay->set("csvquote", $action->getParam("CSV_ENCLOSURE") === "'");
}
