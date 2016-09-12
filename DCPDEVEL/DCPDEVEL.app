<?php

$app_desc = array(
    "name" => "DCPDEVEL",
    "short_name" => N_("DCPDEVEL:DCPDEVEL"),
    "description" => N_("DCPDEVEL:DCPDEVEL"),
    "icon" => "DCPDEVEL.png",
    "with_frame" =>"Y",
    "displayable" => "N",
    "tag" => "ADMIN SYSTEM",
    "childof" => ""
);


$app_acl = array(
    array(
        "name"          => "DEVEL",
        "description"   => N_("dcpdevel:main Access"),
        "admin"         => true
    )
);


// Actions for this application
$action_desc = array(
    array(
        "name"       => "ADMIN_ACTIONS_LIST",
        "short_name" => N_("dcpdevel:ADMIN_ACTIONS_LIST short_name"),
        "script" => "action.actionlist.php",
        "function" => "actionlist",
        "acl" => "DEVEL"
    ),
    array(
        "name" => "DEVELMAIN",
        "short_name" => N_("DCPDEVEL:DEVELMAIN"),
        "script" => "action.develmain.php",
        "layout" => "develmain.html",
        "function" => "develmain",
        "root" => "Y",
        "acl" => "DEVEL"
    )
);

