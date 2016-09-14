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
        "short_name" => N_("DCPDEVEL:DEVELFAMILY"),
        "script" => "action.develmain.php",
        "layout" => "develmain.html",
        "function" => "develmain",
        "root" => "Y",
        "acl" => "DEVEL"
    ),
    array(
        "name" => "FAMILYCONF",
        "short_name" => N_("DCPDEVEL:FAMILYCONF"),
        "script" => "action.familyconf.php",
        "layout" => "familyconf.html",
        "function" => "familyconf",
        "root" => "N",
        "acl" => "DEVEL"
    ),
    array(
        "name" => "FAMILYEXPORTFORM",
        "short_name" => N_("DCPDEVEL:FAMILYEXPORTFORM"),
        "script" => "action.familyexportform.php",
        "layout" => "familyexportform.html",
        "function" => "familyexportform",
        "root" => "N",
        "acl" => "DEVEL"
    ),
    array(
        "name" => "SETLOGICALNAME",
        "short_name" => N_("DCPDEVEL:SETLOGICALNAME"),
        "script" => "action.setlogicalname.php",
        "function" => "setlogicalname",
        "root" => "N",
        "acl" => "DEVEL"
    ),
    array(
        "name" => "SEARCHSYSDOC",
        "short_name" => N_("DCPDEVEL:SEARCHSYSDOC"),
        "script" => "action.searchsysdoc.php",
        "function" => "searchsysdoc",
        "root" => "N",
        "acl" => "DEVEL"
    ),
    array(
        "name" => "MODIFYFAMILY",
        "short_name" => N_("DCPDEVEL:MODIFYFAMILY"),
        "script" => "action.modifyfamily.php",
        "function" => "modifyfamily",
        "root" => "N",
        "acl" => "DEVEL"
    ),
    array(
        "name" => "EXPORTFAMILYCONFIG",
        "short_name" => N_("DCPDEVEL:EXPORTFAMILYCONFIG"),
        "script" => "action.exportfamilyconfig.php",
        "function" => "exportfamilyconfig",
        "root" => "N",
        "acl" => "DEVEL"
    )
);

