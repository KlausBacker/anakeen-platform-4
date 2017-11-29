<?php


include_once("FDL/Class.Doc.php");
include_once("FDL/exportfld.php");
function exportTestRender(Action $action) {

    $outPath=$action->getArgument("output");

    /**
     * @var \Dir $folder
     */
    $folder=\Dcp\HttpApi\V1\DocManager\DocManager::getDocument("SELENIUM_DATA");
    if (! $folder) {
        $folder= \Dcp\HttpApi\V1\DocManager\DocManager::createDocument("DIR");
        $folder->setValue(\Dcp\AttributeIdentifiers\Dir::ba_title, "TEST_DDUI_INIT_DATA");

        $folder->store();
        $folder->setLogicalName("SELENIUM_DATA");
    }

    $folder->clear();

    /**
     * @var \Dir $folder
     */


    $s=new \SearchDoc("", -1);
    $s->addFilter("name ~ '^TST_DDUI_'");
    $s->setOrder("name");
    $familiesData = $s->search();

    foreach ($familiesData as $familyData) {
        insertTestInfoSelenium($folder, $familyData["name"]);
    }



    $s=new SearchDoc("", "TST_RENDER");
    $s->addFilter("name is not null");
    $t=$s->search();
    $folder->insertMultipleDocuments($t);
    setHttpVar("csv-enclosure", '"');

    exportfld($action, $folder->id, "", $outPath);


    //redirect($action, "FREEDOM", "FREEDOM_VIEW&app=FREEDOM&action=FREEDOM_VIEW&dirid="."SELENIUM_DATA");

}

function insertTestInfoSelenium(\Dir $folder, $familyName) {

    $s=new SearchDoc("", $familyName);
    $s->addFilter("name is not null");
    $t=$s->search();
    $folder->insertMultipleDocuments($t);


    $family=\Dcp\HttpApi\V1\DocManager\DocManager::getFamily($familyName);
    if ($family->ccvid) {
        //   $folder->insertDocument($family->ccvid);
    }
}