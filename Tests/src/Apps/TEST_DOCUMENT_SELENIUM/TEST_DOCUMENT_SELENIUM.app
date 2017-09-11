<?php

$app_desc = array(
    "name" => "TEST_DOCUMENT_SELENIUM",
    "short_name" => ("TEST_DOCUMENT_SELENIUM"),
    "description" => ("TEST_DOCUMENT_SELENIUM"),
    "icon" => "TEST_DOCUMENT_SELENIUM.png",
    "with_frame" =>"Y",
    "displayable" => "Y",
    "childof" => ""
);



// Actions for this application
$action_desc = array(
    array(
        "name" => "TESTMAIN",
        "short_name" => "Main test interface",
        "script" => "action.testmain.php",
        "layout" => "testmain.html",
        "function" => "testmain",
        "root" => "Y"
    )
);


