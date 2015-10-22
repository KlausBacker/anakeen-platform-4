<?php

$app_desc = array(
    "name" => "TEST_DOCUMENT",
    "short_name" => N_("DUIT:Document render : unit test"),
    "description" => N_("DUIT:Document render : unit test"),
    "icon" => "documentRender_test.png",
    "displayable" => "N"
);

$app_acl = array(
    array(
        "name" => "AUTOTEST",
        "description" => N_("DUIT:Right to autotest")
    )
);

$action_desc = array(
    array(
        "name" => "JASMINE",
        "short_name" => N_("DUIT:Jasmine"),
        "script" => "action.jasmine.php",
        "function" => "jasmine",
        "acl" => "AUTOTEST"),
    array(
        "name" => "KITCHENSINK",
        "short_name" => N_("DUIT:Render a document"),
        "script" => "action.kitchensink.php",
        "function" => "kitchensink",
        "root" => "Y",
        "acl" => "AUTOTEST"),
    array(
        "name" => "GENERATE_DATA",
        "short_name" => N_("DUIT:Generate test render data"),
        "script" => "action.generate_data.php",
        "function" => "generate_data"
        ),
    array(
     "name" => "GENERATE_VOID_DATA",
     "short_name" => N_("DUIT:Generate void data"),
     "script" => "action.generate_void_data.php",
     "function" => "generate_void_data"
     )
);



