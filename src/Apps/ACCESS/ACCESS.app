<?php

global $app_desc, $action_desc, $app_acl;

$app_desc = array(
    "name" => "ACCESS", //Name
    "short_name" => N_("Access"), //Short name
    "description" => N_("What Access Management"), //long description
    "access_free" => "N", //Access free ? (Y,N)
    "icon" => "access.png", //Icon
    "displayable" => "Y", //Should be displayed on an app list (Y,N)
    "iorder" => 10, // install order
    "tag" => "ADMIN SYSTEM",
    "with_frame" => "Y"
);

$app_acl = array(
    array(
        "name" => "ADMIN",
        "description" => N_("Admin Access"),
        "admin" => TRUE));

$action_desc = array(
    array(
        "name"       => "ADMIN_ACTIONS_LIST",
        "short_name" => N_("access:ADMIN_ACTIONS_LIST short_name"),
        "acl" => "ADMIN"
    ),
    array(
        "name" => "USER_ACCESS",
        "toc_order" => 4,
        "toc" => "Y",
        "acl" => "ADMIN",
        "short_name" => N_("User Access"),
        "root" => "Y"
    ),
    array(
        "name" => "GROUP_ACCESS",
        "toc_order" => 3,
        "toc" => "Y",
        "acl" => "ADMIN",
        "short_name" => N_("Group Access"),
        "layout" => "user_access.xml"
    ),

    array(
        "name" => "ROLE_ACCESS",

        "toc_order" => 2,
        "toc" => "Y",
        "acl" => "ADMIN",
        "short_name" => N_("Role Access"),
        "layout" => "user_access.xml"
    ),
    array(
        "name" => "APPL_ACCESS",
        "toc" => "Y",
        "toc_order" => 1,
        "acl" => "ADMIN",
        "layout" => "user_access.xml",
        "short_name" => N_("Application Access")
    ),
    array(
        "name" => "MODIFY",
        "acl" => "ADMIN",
        "short_name" => N_("Modify any access")
    ),
    array(
        "name" => "DOWNLOAD",
        "acl" => "ADMIN"
    ),
    array(
        "name" => "UPLOAD",
        "acl" => "ADMIN"
    ),
    array(
        "name" => "IMPORT_EXPORT",
        "toc" => "Y",
        "toc_order" => 5,
        "acl" => "ADMIN",
        "short_name" => N_("Import/Export")
    ),
    array(
        "name" => "EDIT",
        "short_name" => N_("Edit any access"),
        "acl" => "ADMIN"
    ),
    array(
        "name" => "GET_ACCOUNT",
        "acl" => "ADMIN",
        "toc" => "N",
        "short_name" => N_("Get account"),
        "function" => "accessGetAccounts",
        "script" => "haccess.php"
    ),
    array(
        "name" => "GET_APPS",
        "acl" => "ADMIN",
        "toc" => "N",
        "short_name" => N_("Get application"),
        "function" => "accessGetApps",
        "script" => "haccess.php"
    ),
    array(
        "name" => "GET_DATATABLE_INFO",
        "acl" => "ADMIN",
        "toc" => "N",
        "short_name" => N_("Get datatable information"),
        "function" => "accessGetDatatableInfo",
        "script" => "haccess.php"
    ),
    array(
        "name" => "USER_GET_DATATABLE_INFO",
        "acl" => "ADMIN",
        "toc" => "N",
        "short_name" => N_("Get datatable information for User, group or role"),
        "function" => "accessUserGetDatatableInfo",
        "script" => "haccess.php"
    ),
    array(
        "name" => "GET_ACCOUNTTYPES_IMAGE",
        "acl" => "ADMIN",
        "toc" => "N",
        "short_name" => N_("Get accounttypes image"),
        "function" => "accessGetAccounttypesImage",
        "script" => "haccess.php"
    )
);

?>
