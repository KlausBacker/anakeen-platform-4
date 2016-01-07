<?php

global $app_const;

$app_const = array(
    "VERSION" => "{{VERSION}}-{{RELEASE}}",

    "TESTFAMILIES" => array(
        "descr" => "Testing Families",
        "static"=>"yes",
        "val" => json_encode(array("TST_DDUI_ALLTYPE", "TST_DDUI_ENUM"))
    )
);
