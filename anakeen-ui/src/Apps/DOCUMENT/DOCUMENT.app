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
    ),
    array(
        "name" => "ADMIN",
        "description" => N_("ddui:Admin")
    )
);

$action_desc = array(


    array(
        "name" => "COLLECT_ERROR",
        "short_name" => N_("ddui:COLLECT_ERROR"),
        "script" => "action.collect_error.php",
        "function" => "collect_error",
    )

);



