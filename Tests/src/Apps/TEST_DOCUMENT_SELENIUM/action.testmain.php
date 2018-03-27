<?php


function testMain(\Anakeen\Core\Internal\Action $action) {
    $smartElement = \Dcp\UI\UIGetAssetPath::getSmartElement();
    $ws = \Dcp\UI\UIGetAssetPath::getWs();

    foreach ($smartElement["js"] as $currentPath) {
        $action->parent->addJsRef($currentPath);
    }
    $action->parent->addJsRef("TEST_DOCUMENT_SELENIUM/dist/testmain.js?ws=".$ws);

    foreach ($smartElement["css"] as $currentPath) {
        $action->parent->AddCssRef($currentPath);
    }
    $action->parent->AddCssRef("css/ank/document/bootstrap.css?ws=".$ws);

}