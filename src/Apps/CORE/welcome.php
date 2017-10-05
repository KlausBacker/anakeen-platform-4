<?php
/*
 * @author Anakeen
 * @package FDL
*/

function welcome(Action &$action)
{
    
    $action->parent->AddCssRef("CORE:welcome.css");
    $action->lay->set("thisyear", strftime("%Y", time()));
    $action->lay->set("version", $action->GetParam("VERSION"));
    $action->lay->set("userRealName", $action->user->firstname . " " . $action->user->lastname);
    $action->lay->set("userDomain", getParam("CORE_CLIENT"));
    $action->lay->set("isAdmin", (file_exists('admin/index.php') && $action->canExecute("CORE_ADMIN_ROOT", "CORE_ADMIN") === ''));

}
