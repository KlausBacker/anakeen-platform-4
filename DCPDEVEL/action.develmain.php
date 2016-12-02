<?php
function develmain(Action & $action)
{
    
    $action->parent->addJsRef("lib/jquery-ui-1.12.0/external/jquery/jquery.js");
    $action->parent->addJsRef("lib/jquery-ui-1.12.0/jquery-ui.js");
    $action->parent->addJsRef("lib/jquery-dataTables/1.10/js/jquery.dataTables.js");
    $action->parent->addJsRef("DCPDEVEL/Layout/develmain.js");
    
    $action->parent->addCssRef("WHAT/Layout/size-normal.css");
    $action->parent->addCssRef("lib/jquery-ui-1.12.0/jquery-ui.css");
    //  $action->parent->addCssRef("lib/jquery-ui-1.12.0/jquery-ui.structure.css");
    //  $action->parent->addCssRef("lib/jquery-ui-1.12.0/jquery-ui.theme.min.css");
    $action->parent->addCssRef("lib/jquery-dataTables/1.10/css/jquery.dataTables.css");
    $action->parent->addCssRef("lib/jquery-dataTables/1.10/css/dataTables.jqueryui.css");
    
    $action->parent->addCssRef("DCPDEVEL/Layout/develmain.css");
    
    $families = new SearchDoc("", -1);
    $families->setObjectReturn(true);
    $families->search();
    
    $familyList = $families->getDocumentList();
    
    $familyData = [];
    foreach ($familyList as $family) {
        $familyData[] = ["label" => $family->getTitle() , "icon" => $family->getIcon("", 22) , "familyid" => $family->id, "familyname" => $family->name];
    }
    
    $action->lay->eSetBlockData("families", $familyData);
}
