<?php

$app_desc = array(
    "name" => "BUSINESS_APP",
    "short_name" => N_("BUSINESS_APP:BUSINESS_APP"),
    "description" => N_("BUSINESS_APP:BUSINESS_APP"),
    "icon" => "BUSINESS_APP.png",
    "with_frame" =>"Y",
    "displayable" => "N",
    "childof" => ""
);


// ACLs for this application
$app_acl = array(
    array(
        "name" => "BASIC",
        "description" => N_("BUSINESS_APP:Basic ACL")
    )
);
// Actions for this application
$action_desc = array(
    array(
        "name" => "MAIN",
        "short_name" => N_("MAIN"),
        "script" => "action.main.php",
        "layout" => "main.html",
        "function" => "main",
        "root" => "Y",
        "acl" => "BASIC"
    )
);


