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
        "acl" => "VIEW_RENDER"),
    array(
        "name" => "TEMPLATE",
        "short_name" => N_("Get some template"),
        "script" => "action-template.php",
        "function" => "template",
        "acl" => "VIEW_RENDER")
);



