<?php

$app_desc = array(
    "name"        => "CORE_ADMIN",
    "short_name"  => N_("core_admin:Access to administration application"),
    "description" => N_("core_admin:Unified interface exposing all admin applications"),
    "icon"        => "core_admin.png",
    "displayable" => "N",
    "with_frame"  => "Y",
    "childof"     => "",
    "tag"         => "ADMIN"
);

/* ACLs for this application */
$app_acl = array(
    array(
        "name"        => "CORE_ADMIN",
        "description" => N_("core_admin:Access to CORE_ADMIN")
    )
);

/* Actions for this application */
$action_desc = array(
    array(
        "name"       => "CORE_ADMIN_ROOT", 
        "short_name" => N_("core_admin:Main ihm"),
        "script"     => "core_admin_root.php", 
        "function"   => "core_admin_root", 
        "layout"     => "core_admin_root.html",
        "acl"        => "CORE_ADMIN",
        "root"       => "Y"
    )
);

?>
