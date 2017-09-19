<?php

global $app_desc, $action_desc, $app_acl;

$app_desc = array(
    "name" => "DOCADMIN",
    "short_name" => N_("docadmin:manage documents"),
    "description" => N_("docadmin:ihm to access and manage documents"),
    "access_free" => "N",
    "icon" => "docadmin.png",
    "displayable" => "Y",
    "iorder" => 10,
    "tag" => "ADMIN SYSTEM",
    "with_frame" => "Y"
);

$app_acl = array(
    array(
        "name"          => "DOCADMIN",
        "description"   => N_("docadmin:DOCADMIN Access"),
        "admin"         => true
    ),
    array(
        "name"          => "EXPORT",
        "description"   => N_("docadmin:Export Access"),
        "admin"         => true
    )
);

$action_desc = array(
    array(
        "name"       => "ADMIN_ACTIONS_LIST",
        "short_name" => N_("docadmin:ADMIN_ACTIONS_LIST short_name"),
        "acl" => "DOCADMIN"
    ),
    array(
        "name"       => "DOCS_ADMIN",
        "acl"        => "DOCADMIN",
        "short_name" => N_("docadmin:Documents management"),
        "script"     => "docs_admin.php",
        "function"   => "docs_admin",
        "layout"     => "docs_admin.html",
        "root"       => "Y"
    ),
    array(
        "name"       => "TIMERS_ADMIN",
        "acl" => "DOCADMIN",
        "short_name" => N_("docadmin:Timers management"),
        "script"     => "timers_admin.php",
        "function"   => "timers_admin",
        "layout"     => "timers_admin.xml"
    ),

    array(
     "name"		=>"TIMERS_ADMIN_RESULT",
     "short_name"		=>N_("Timers management result"),
     "acl"		=>"DOCADMIN"
    ),
    array(
        "name"       => "SYSTEM_DOCS",
        "acl" => "DOCADMIN",
        "short_name" => N_("docadmin:View system document"),
        "script"     => "system_docs.php",
        "function"   => "system_docs",
        "layout"     => "system_docs.html"
    ),
    array(
        "name"       => "SYSTEM_GETDATA",
        "acl" => "DOCADMIN",
        "short_name" => N_("docadmin:View system document")
    ),
    array(
        "name"       => "EXPORTFOLDER",
        "acl" => "EXPORT",
        "short_name" => N_("docadmin:Export"),
        "script"     => "export_folder.php",
        "function"   => "export_folder"
    ),
    array(
        "name"       => "FAMILY_VIEWATTRIBUTES",
        "acl" => "DOCADMIN",
        "short_name" => N_("docadmin:Family attributes"),
        "script"     => "family_viewattributes.php",
        "function"   => "familyViewAttributes",
        "layout"     => "family_viewattributes.html"
    ),
    array(
        "name"       => "FAMILY_MODATTRIBUTE",
        "acl" => "DOCADMIN",
        "short_name" => N_("docadmin:Modify Family attribute"),
        "script"     => "family_modattribute.php",
        "function"   => "familyModAttribute"
    )
);
