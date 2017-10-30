<?php


include_once("FDL/Class.Doc.php");
function exportTestRender(Action $action) {

    $folder=\Dcp\HttpApi\V1\DocManager\DocManager::getDocument("SELENIUM_DATA");
    if (! $folder) {
        $folder= \Dcp\HttpApi\V1\DocManager\DocManager::createDocument("DIR");
        $folder->setValue(\Dcp\AttributeIdentifiers\Dir::ba_title, "Selenium Data");
        $folder->store();
        $folder->setLogicalName("SELENIUM_DATA");
    }

    /**
     * @var \Dir $folder
     */


    insertTestInfoSelenium($folder, "TST_DDUI_ENUM");
    insertTestInfoSelenium($folder,"TST_DDUI_DOCID");
    insertTestInfoSelenium($folder,"TST_DDUI_ALLTYPE");
    insertTestInfoSelenium($folder,"TST_DDUI_EMPLOYEE");


    $s=new SearchDoc("", "TST_RENDER");
    $t=$s->search();
    $folder->insertMultipleDocuments($t);







    redirect($action, "FREEDOM", "FREEDOM_VIEW&app=FREEDOM&action=FREEDOM_VIEW&dirid="."SELENIUM_DATA");

}

function insertTestInfoSelenium(\Dir $folder, $familyName) {


    $s=new SearchDoc("", $familyName);
    $s->addFilter("name is not null");
    $t=$s->search();
    $folder->insertMultipleDocuments($t);

    $family=\Dcp\HttpApi\V1\DocManager\DocManager::getFamily($familyName);
    $folder->insertDocument($family->ccvid);
}