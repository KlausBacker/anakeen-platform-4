<?php

$app_desc = array(
    "name" => "DOCUMENT_GRID_HTML5",
    "short_name" => N_("DCOGRIDHTML5: Document_grid_ui"),
    "description" => N_("DCOGRIDHTML5: Document_grid_ui"),
    "access_free" => "N",
    "icon" => "document_grid_html5.png",
    "displayable" => "N",
    "with_frame" => "N",
    "childof" => ""
);

/* ACLs for this application */
$app_acl = array(
    array(
        "name" => "BASIC",
        "description" => N_("DOCGRID: Access to document grid element")
    )
);

/* Actions for this application */
$action_desc = array(
    array(
        "name" => "GETDATATABLELOCAL",
        "acl" => "BASIC"
    ),
    array(
        "name" => "GETLOCAL",
        "acl" => "BASIC"
    ),
    array(
        "name" => "KITCHEN_SINK",
        "short_name" => N_("DCOGRIDHTML5:KITCHEN_SINK"),
        "layout" => "kitchen_sink.html",
        "script" => "action.kitchen_sink.php",
        "function" => "kitchen_sink",
        "root" => "Y",
    )
);