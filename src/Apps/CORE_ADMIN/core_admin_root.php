<?php
/*
 * @author Anakeen
 * @package FDL
*/

require_once "FDL/freedom_util.php";

function core_admin_root(Action & $action)
{
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    $action->parent->addCssRef("CORE_ADMIN/Layout/core_admin_root.css");
    
    $adminApps = array();
    $query = <<< 'SQL'
SELECT
    application.name,
    application.id,
    application.icon,
    application.short_name,
    application.description,
    application.with_frame,
    root_action.acl,
    root_action.name as root_action,
    list_action.name as admin_actions_list
FROM application
LEFT JOIN action as root_action
ON application.id = root_action.id_application AND root_action.root = 'Y'
LEFT JOIN action as list_action
ON application.id = list_action.id_application AND list_action.name = 'ADMIN_ACTIONS_LIST'
WHERE
    application.tag ~* E'\\yadmin\\y'
    AND application.available = 'Y'
    AND application.name != 'CORE_ADMIN'
;
SQL;
    
    simpleQuery('', $query, $adminApps, false, false, true);
    
    $admin_apps = array();
    foreach ($adminApps as $adminApp) {
        if ($action->user->id != 1) { // no control for user Admin
            if (!$action->HasPermission($adminApp["acl"], $adminApp["id"])) {
                continue;
            }
        }
        if (empty($adminApp["admin_actions_list"]) && empty($adminApp["root_action"])) {
            continue; // no one available actions detected
            
        }
        $appUrl = "?app=" . $adminApp["name"];
        if ($adminApp["with_frame"] !== 'Y') {
            $appUrl.= "&sole=A";
        }
        $admin_apps[] = array(
            "APPLICATION_NAME" => $adminApp["name"],
            "APPLICATION_URL" => $appUrl,
            "APPLICATION_ICON_SRC" => $action->parent->getImageLink($adminApp["icon"], false, 30) ,
            "APPLICATION_ICON_ALT" => $adminApp["name"],
            "APPLICATION_TITLE" => _($adminApp["short_name"]) ,
            "APPLICATION_DESC" => _($adminApp["description"]) ,
            "ROOT_ACTION" => $adminApp["root_action"],
            "HAS_ADMIN_ACTIONS" => !empty($adminApp["admin_actions_list"])
        );
    }
    
    $sortFunction = function ($value1, $value2)
    {
        return strnatcasecmp($value1["APPLICATION_TITLE"], $value2["APPLICATION_TITLE"]);
    };
    
    usort($admin_apps, $sortFunction);
    
    $action->lay->setBlockData('ADMIN_APPS', $admin_apps);
    
    $user = new_Doc('', $action->user->fid);
    $action->lay->eSet("USER_NAME", $user->getTitle());
    /**
     * Add widget code
     */
    $action->lay->set("WIDGET_PASSWORD", $action->parent->getJsLink("CORE:dcpui.passwordmodifier.js.xml", true));
    /**
     * Test if can change password
     */
    $action->lay->set('DISPLAY_CHANGE_BUTTON', ("" === $user->canEdit()));
}
?>
