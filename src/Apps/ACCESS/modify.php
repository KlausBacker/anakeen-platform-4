<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: modify.php,v 1.7 2007/02/14 15:13:16 eric Exp $
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: modify.php,v 1.7 2007/02/14 15:13:16 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/modify.php,v $
// ---------------------------------------------------------------
// -----------------------------------
function modify(Action & $action)
{
    // -----------------------------------
    modify_app($action);
}
// -----------------------------------
function modify_app(Action & $action)
{
    // -----------------------------------
    // get all parameters
    $userId = $action->getArgument("userid");
    $appId = $action->getArgument("appid");
    $aclp = $action->getArgument("aclup"); // ACL + (more access)
    $acln = $action->getArgument("aclun"); // ACL - (less access)
    // modif permission for a uncontrolled object
    $p = new Permission($action->dbaccess, array(
        $userId,
        $appId
    ));
    if (!$p->IsAffected()) {
        $p->Affect(array(
            "id_user" => $userId,
            "id_application" => $appId
        ));
    }
    // delete old permissions
    $p->deletePermission($userId, $appId, null, null);
    $p->deletePermission(null, $appId, null, true);
    
    if (is_array($aclp)) {
        // create new permissions
        foreach ($aclp as $v) {
            $p->id_acl = $v;
            $p->computed = false;
            $p->Add();
        }
    }
    
    if (is_array($acln)) {
        // create new permissions
        foreach ($acln as $v) {
            $p->id_acl = - $v;
            $p->computed = false;
            $p->Add();
        }
    }
    
    $action->lay->template = json_encode(array(
        "success" => true
    ));
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}
?>
