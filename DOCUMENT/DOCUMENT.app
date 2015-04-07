<?php

$app_desc = array(
    "name" => "DOCUMENT",
    "short_name" => N_("ddui:Document render"),
    "description" => N_("ddui:Document render long"),
    "icon" => "documentRender.png",
    "displayable" => "N"
);

$app_acl = array(
    array(
        "name" => "VIEW_RENDER",
        "description" => N_("ddui:View documents")
    )
);

$action_desc = array(
    array(
        "name" => "VIEW",
        "short_name" => N_("ddui:Render a document"),
        "layout" => "view.html",
        "script" => "action-view.php",
        "function" => "view",
        "root" => "Y",
        "acl" => "VIEW_RENDER"
    ),
    array(
        "name" => "SUBMENU",
        "short_name" => N_("ddui:Get dynamic sub menu"),
        "script" => "action-submenu.php",
        "function" => "submenu",
        "acl" => "VIEW_RENDER"
    ),
    array(
        "name" => "AUTOCOMPLETE",
        "short_name" => N_("ddui:Autocomplete attributes"),
        "script" => "action-autocomplete.php",
        "function" => "autocomplete",
        "acl" => "VIEW_RENDER"
    ),
    array(
        "name" => "TEMPLATE",
        "short_name" => N_("ddui:Get some template"),
        "script" => "action-template.php",
        "function" => "template"
    ),
    array(
        "name" => "WRAP_KENDO",
        "short_name" => N_("ddui:Wrap kendo"),
        "script" => "action.wrap_kendo.php",
        "function" => "wrap_kendo",
        "layout" => "loaderKendo.js"
    ),
    //region full
    array(
        "name" => "COLLECT_ERROR",
        "short_name" => N_("ddui:COLLECT_ERROR"),
        "script" => "action.collect_error.php",
        "function" => "collect_error",
    )
    //endregion full

);



