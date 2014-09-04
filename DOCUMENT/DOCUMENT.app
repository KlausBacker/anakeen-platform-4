<?php

$app_desc = array(
    "name" => "DOCUMENT",
    "short_name" => N_("Document render"),
    "description" => N_("Document render"),
    "icon" => "documentRender.png",
    "displayable" => "N"
);

$app_acl = array(
    array(
        "name" => "VIEW_RENDER",
        "description" => N_("View documents")
    )
);

$action_desc = array(
    array(
        "name" => "VIEW",
        "short_name" => N_("Render a document"),
        "layout" => "view.html",
        "script" => "action-view.php",
        "function" => "view",
        "root" => "Y",
        "acl" => "VIEW_RENDER"),
    array(
        "name" => "SUBMENU",
        "short_name" => N_("Get dynamic sub menu"),
        "script" => "action-submenu.php",
        "function" => "submenu",
        "acl" => "VIEW_RENDER"),
    array(
        "name" => "AUTOCOMPLETE",
        "short_name" => N_("Autocomplete attributes"),
        "script" => "action-autocomplete.php",
        "function" => "autocomplete",
        "acl" => "VIEW_RENDER"),
    array(
        "name" => "TEMPLATE",
        "short_name" => N_("Get some template"),
        "script" => "action-template.php",
        "function" => "template"),
    array(
        "name" => "WRAP_KENDO",
        "short_name" => N_("Wrap kendo"),
        "script" => "action.wrap_kendo.php",
        "function" => "wrap_kendo",
        "layout" => "loaderKendo.js"
    )

);



