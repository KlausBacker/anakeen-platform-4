<?php
/*
 * package FDL
*/

global $app_const;

$app_const = array(
    "VERSION" => "1.1.0-0",
    
    "TESTFAMILIES" => array(
        "descr" => "Testing Families",
        "static" => "yes",
        "val" => json_encode(array(
            "TST_DDUI_ALLTYPE",
            "TST_DDUI_ENUM",
            "TST_DDUI_DOCID",
            "TST_DDUI_EMPLOYEE"
        ))
    )
);
