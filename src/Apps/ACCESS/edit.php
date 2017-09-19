<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: edit.php,v 1.12 2007/02/14 13:22:58 eric Exp $
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: edit.php,v 1.12 2007/02/14 13:22:58 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/edit.php,v $
// ---------------------------------------------------------------
// ---------------------------------------------------------------
// -----------------------------------
function edit(Action & $action)
{
    // -----------------------------------
    $accountType = $action->getArgument("accountType");
    // the modification can come from action user_access or appl_access
    if ($action->getArgument("mod") == "user") {
        $appId = $action->getArgument("id");
        if ($accountType == "G") {
            $userId = $action->getArgument("access_group_id");
        } elseif ($accountType == "R") {
            $userId = $action->getArgument("access_role_id");
        } else {
            $userId = $action->getArgument("access_user_id");
        }
    } else {
        $userId = $action->getArgument("id");
        $appId = $action->getArgument("access_appl_id");
    }
    $action->lay->Set("modifyact", "MODIFY");
    $action->lay->Set("target", "fbody");
    // write title : user name
    $user = new Account($action->dbaccess, $userId);
    switch ($user->accounttype) {
        case "U":
            $action->lay->set("accountLabel", _("User"));
            break;

        case "G":
            $action->lay->set("accountLabel", _("access:Group"));
            break;

        case "R":
            $action->lay->set("accountLabel", _("Role"));
            break;

        default:
            $action->lay->set("accountLabel", "");
    }
    $action->lay->eSet("title", $user->firstname . " " . $user->lastname);
    edit_main($action, $userId, $appId);
}
// -----------------------------------
function edit_main(Action & $action, $userId, $appId, $coid = 0)
{
    // ------------------------
    //  print "$userId -  $appId - $coid";
    // Get all the params
    if (!$appId) $action->exitError(_("Cannot edit access. No application parameter."));
    if (!$userId) $action->exitError(_("Cannot edit access. No user parameter."));
    
    $action->lay->Set("nbinput", 4);
    $action->lay->eSet("userid", $userId);
    $action->lay->eSet("oid", $coid);
    $action->lay->eSet("appid", $appId);
    $action->lay->Set("dboperm", "");
    //-------------------
    // compute permission
    $app = new Application($action->dbaccess, $appId);
    $action->lay->eSet("appname", $action->text($app->short_name));
    $uperm = new Permission($action->dbaccess, array(
        $userId,
        $appId
    ));
    $acl = new Acl($action->dbaccess);
    
    $appacls = $acl->getAclApplication($appId);
    
    $tableacl = array();
    foreach ($appacls as $k => $v) {
        
        $tableacl[$k]["aclname"] = $v->name;
        $tableacl[$k]["acldesc"] = " (" . _($v->description) . ")";
        $tableacl[$k]["aclid"] = $v->id;
        if ($uperm->HasPrivilege($v->id)) {
            $tableacl[$k]["selected"] = "checked";
        } else {
            $tableacl[$k]["selected"] = "";
        }
        $tableacl[$k]["iacl"] = "$k"; // index for table in xml
        if (in_array($v->id, $uperm->GetUnPrivileges())) {
            $tableacl[$k]["selectedun"] = "checked";
        } else {
            $tableacl[$k]["selectedun"] = "";
        }
        if (in_array($v->id, $uperm->GetUpPrivileges())) {
            $tableacl[$k]["selectedup"] = "checked";
        } else {
            $tableacl[$k]["selectedup"] = "";
        }
        if (in_array($v->id, $uperm->GetGPrivileges())) {
            $tableacl[$k]["selectedg"] = "checked";
        } else {
            $tableacl[$k]["selectedg"] = "";
        }
    }
    
    $action->lay->SetBlockData("SELECTACL", $tableacl);
}
