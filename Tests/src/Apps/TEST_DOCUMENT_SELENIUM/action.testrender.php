<?php


function testRender(\Anakeen\Core\Internal\Action $action) {

    $smartElement = \Dcp\UI\UIGetAssetPath::getSmartElement();
    $ws = \Dcp\UI\UIGetAssetPath::getWs();

    $action->parent->addJsRef(\Dcp\UI\UIGetAssetPath::getJSJqueryPath());
    $action->parent->addJsRef(\Dcp\UI\UIGetAssetPath::getJSKendoPath());
    $action->parent->addJsRef("components/dist/ank-components.js?ws=".$ws);


    foreach ($smartElement["js"] as $currentPath) {
      //  $action->parent->addJsRef($currentPath);
    }
    $action->parent->addJsRef("TEST_DOCUMENT_SELENIUM/dist/testrender.js?ws=".$ws);

    foreach ($smartElement["css"] as $currentPath) {
        $action->parent->AddCssRef($currentPath);
    }
    $action->parent->AddCssRef("css/ank/document/bootstrap.css?ws=".$ws);
}