<?php

global $app_desc, $app_acl, $action_desc;
$app_desc = array(
    "name"        => "APPMNG", //Name
    "short_name"  => N_("Application manager"), //Short name
    "description" => N_("What Application Management"), //long description
    "access_free" => "N", //Access free ? (Y,N)
    "icon"        => "appmng.png", //Icon
    "displayable" => "N", //Should be displayed on an app list (Y,N)
    "iorder"      => 10, // install order
    "tag"         => "ADMIN SYSTEM",// install order
    "with_frame" => "Y"
);

$app_acl = array(
    array(
        "name"        => "ADMIN",
        "description" => N_("SuperUser permission"),
        "admin"       => TRUE)
);

$action_desc = array(
    array(
        "name" => "ADMIN_ACTIONS_LIST",
        "short_name" => N_("appmng:ADMIN_ACTIONS_LIST short_name"),
        "acl" => "ADMIN"
    ),
    array(
        "name"       => "PARAM_ALIST",
        "short_name" => N_("application parameters"),
        "toc"        => "Y",
        "toc_order"  => 1,
        "acl"        => "ADMIN"
    ),
    array(
        "name"       => "PARAM_ULIST",
        "short_name" => N_("user parameters"),
        "toc"        => "Y",
        "toc_order"  => 5,
        "acl"        => "ADMIN"
    ),
    array(
        "name"       => "ACTIONLIST",
        "short_name" => N_("actions"),
        "toc"        => "Y",
        "toc_order"  => 4,
        "acl"        => "ADMIN"
    ),
    array(
        "name"       => "APPLIST",
        "toc"        => "Y",
        "toc_order"  => 3,
        "short_name" => N_("applications"),
        "acl"        => "ADMIN",
        "root" => "Y"
    ),
    array(
        "acl"  => "ADMIN",
        "name" => "APP_EDIT"
    ),
    array(
        "acl"  => "ADMIN",
        "name" => "ACTION_MOD"
    ),
    array(
        "acl"  => "ADMIN",
        "name" => "APP_MOD"
    ),
    array(
        "acl"  => "ADMIN",
        "name" => "APP_UPDATE"
    ),
    array(
        "acl"      => "ADMIN",
        "name"     => "APP_UPDATEALL",
        "function" => "app_updateAll",
        "script"   => "app_update.php"
    ),
    array(
        "acl"  => "ADMIN",
        "name" => "APP_DELETE"
    ),
    array(
        "acl"  => "ADMIN",
        "name" => "ACTION_EDIT"
    ),
    array(
        "acl"        => "ADMIN",
        "short_name" => N_("delete parameters"),
        "name"       => "PARAM_DELETE"
    ),
    array(
        "acl"        => "ADMIN",
        "short_name" => N_("modify parameters"),
        "name"       => "PARAM_MOD"
    ),
    array(
        "name" => "GET_APPS",
         "acl"  => "ADMIN",
         "function"   => "appmngGetApps",
        "script"     => "actionlist.php"
    ), array(
        "name" => "GET_APPS_PARAMS",
         "acl"  => "ADMIN",
         "function"   => "appmngGetAppsParam",
        "script"     => "param_list.php"
    ),
    array(
        "name" => "GET_DATATABLE_INFO",
         "acl"  => "ADMIN",
         "function"   => "appmngGetDatatableInfo",
        "script"     => "actionlist.php"
    ),
    array(
        "name" => "GET_APP_DATATABLE_INFO",
         "acl"  => "ADMIN",
         "function"   => "appmngGetAppDatatableInfo",
        "script"     => "applist.php"
    ),
    array(
        "name" => "GET_PARAM_LIST_DATATABLE_INFO",
         "acl"  => "ADMIN",
         "function"   => "appmngGetParamListDatatableInfo",
        "script"     => "param_list.php"
    ),
    array(
        "name" => "GET_USERS",
         "acl"  => "ADMIN",
         "function"   => "appmngGetUsers",
        "script"     => "param_ulist.php"
    )
);

?>
