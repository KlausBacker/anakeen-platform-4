<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: param_delete.php,v 1.7 2006/06/22 16:19:07 eric Exp $
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// -----------------------------------
function param_delete(Action & $action)
{
    // -----------------------------------
    $name = $action->getArgument("id");
    $appid = $action->getArgument("appid");
    $atype = $action->getArgument("atype", PARAM_APP);
    
    $parametre = new Param($action->dbaccess, array(
        $name,
        $atype,
        $appid
    ));
    if ($parametre->isAffected()) {
        $action->log->info(_("Remove parameter") . $parametre->name);
        $err = $parametre->Delete();
    } else {
        $err = sprintf(_("the '%s' parameter cannot be removed") , $name);
        $action->addLogMsg($err);
    }
    
    $action->lay->template = json_encode(array(
        "success" => $err ? false : true,
        "error" => $err
    ));
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}
?>
