<?php
/*
 * @author Anakeen
 * @package FDL
*/

global $app_desc, $action_desc, $app_acl;

$app_desc = array(
    "name" => "TENGINE_MONITOR",
    "short_name" => N_("TE:Monitor:short_name") ,
    "description" => N_("TE:Monitor:description") ,
    "displayable" => "N",
    "tag" => "ADMIN SYSTEM",
    "icon" => "tengine_monitor.png",
    "childof"  =>"TENGINE_CLIENT"  
);

$app_acl = array(
);

$action_desc = array(
    array(
        "name" => "ADMIN_ACTIONS_LIST",
        "short_name" => N_("TE:Monitor:admin_actions_list:short_name") ,
        "acl" => "TENGINE_CLIENT"
    ) ,
    array(
        "short_name" => N_("TE:Client:UI:X0010 short name (convert)") ,
        "long_name" => N_("TE:Client:UI:X0010 long name (convert)") ,
        "name" => "TENGINE_MONITOR_CONVERT_FILE",
        "acl" => "TENGINE_CLIENT",
        "script" => "tengine_monitor_convert.php",
        "function" => "tengine_monitor_convert",
        "layout" => "tengine_monitor_convert.html"
    ),
    array(
        "short_name" => N_("TE:Client:UI:X0020 short name (selftests)") ,
        "long_name" => N_("TE:Client:UI:X0020 long name (selftests)") ,
        "name" => "TENGINE_MONITOR_SELFTESTS",
        "acl" => "TENGINE_CLIENT",
        "script" => "tengine_monitor_selftests.php",
        "function" => "tengine_monitor_selftests",
        "layout" => "tengine_monitor_selftests.html"
    ),
    array(
        "short_name" => N_("TE:Client:UI:X0030 short name (tasks)") ,
        "long_name" => N_("TE:Client:UI:X0030 long name (tasks)") ,
        "name" => "TENGINE_MONITOR_TASKS",
        "acl" => "TENGINE_CLIENT",
        "script" => "tengine_monitor_tasks.php",
        "function" => "tengine_monitor_tasks",
        "layout" => "tengine_monitor_tasks.html",
    ) ,
);
