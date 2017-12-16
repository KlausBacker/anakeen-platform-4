<?php
global $app_const;

$loggerClass = <<<'JSON'
[
    "\\Dcp\\UI\\Logger\\JS\\Dcp"
]
JSON;

$app_const = array(
    "INIT" => "yes",
    "VERSION" => "{{VERSION}}-{{RELEASE}}",
    "LOGGER" => array(
        "descr" => N_("ddui:logging class") ,
        "val" => "$loggerClass"
    ) ,
    "ACTIVATE_LOGGING" => array(
        "val" => "TRUE",
        "descr" => N_("ddui:activate logging") ,
        "kind" => "enum(FALSE|TRUE)"
    ) ,
    "MODE_DEBUG" => array(
        "val" => "FALSE",
        "descr" => N_("ddui:debug mode") ,
        "kind" => "enum(FALSE|TRUE)"
    ) ,
    "RENDER_PARAMETERS" => array(
        "val" => "{}",
        "descr" => N_("ddui:render parameters")
    ) ,
    "NOTIFICATION_DELAY" => array(
        "val" => "5000",
        "descr" => N_("ddui:Delay notifications display (in ms)")
    )
    
);
