<?php

function develmain(Action &$action) {


    $action->parent->addJsRef("lib/jquery/jquery.js");
    $action->parent->addJsRef("lib/jquery-ui/js/jquery-ui.js");
    $action->parent->addJsRef("lib/jquery-dataTables/js/jquery.dataTables.js");
    $action->parent->addJsRef("DCPDEVEL/Layout/develmain.js");

    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    $action->parent->addCssRef("lib/jquery-dataTables/css/jquery.dataTables.css");
    $action->parent->addCssRef("DCPDEVEL/Layout/develmain.css");

    $families=new SearchDoc("", -1);
    $families->setObjectReturn(true);
    $families->search();

    $familyList=$families->getDocumentList();

    $familyData=[];
    foreach ($familyList as $family) {
        $familyData[]=[
            "label"=>$family->getTitle(),
            "icon"=>$family->getIcon("", 32),
            "familyid"=>$family->name
        ];
    }



    $action->lay->eSetBlockData("families", $familyData);

}