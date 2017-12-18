<?php

$app_desc = array(
    "name" => "SEARCH_UI_HTML5",
    "short_name" => N_("SEARCH_UI_HTML5:SEARCH_UI_HTML5"),
    "description" => N_("SEARCH_UI_HTML5:SEARCH_UI_HTML5"),
    "icon" => "SEARCH_UI_HTML5.png",
    "displayable" => "N",
    "childof" => ""
);

// ACLs for this application
$app_acl = array(
    array(
        "name" => "BASIC",
        "description" => N_("SEARCH_UI_HTML5:Basic ACL"),
    )
);

// Actions for this application
$action_desc = array(

    array(
	"name" => "RESULT",
	"short_name" => N_("RESULT:ACTION_NAME"),
	"layout" => "result.html",
	"script" => "action.result.php",
	"function" => "main",
    )



);


