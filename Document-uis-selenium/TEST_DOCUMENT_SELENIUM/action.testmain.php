<?php


function testMain(Action $action) {
    $action->parent->addJsRef("lib/RequireJS/require.js");
    $action->parent->addJsRef("lib/underscore/underscore.js");
    $action->parent->addJsRef("lib/KendoUI/2014.3/js/jquery.js");
    $action->parent->addJsRef("lib/bootstrap/3/js/bootstrap.js");
    //$action->parent->addJsRef("lib/jquery-dataTables/1.10/js/jquery.dataTables.js");
    $action->parent->addJsRef("TEST_DOCUMENT_SELENIUM/Layout/testmain.js");

    $action->parent->AddCssRef("css/dcp/document/bootstrap.css");
    $action->parent->AddCssRef("TEST_DOCUMENT_SELENIUM/Layout/testmain.css");


} 