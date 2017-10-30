<?php


function testRender(Action $action) {
    $action->parent->addJsRef("uiAssets/externals/RequireJS/require.js");
    $action->parent->addJsRef("uiAssets/externals/underscore/underscore.js");
    $action->parent->addJsRef("uiAssets/externals/jquery/jquery.js");
    $action->parent->addJsRef("TEST_DOCUMENT_SELENIUM/Layout/testrender.js");

    $action->parent->AddCssRef("css/a4/document/bootstrap.css");
    $action->parent->AddCssRef("TEST_DOCUMENT_SELENIUM/Layout/testrender.css");


}