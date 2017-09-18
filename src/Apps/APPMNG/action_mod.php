<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Modify action parameters
 *
 * @author Anakeen
 * @version $Id: action_mod.php,v 1.4 2005/07/08 15:29:51 eric Exp $
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// -----------------------------------
function action_mod(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $id = $action->getArgument("id");
    $appl_id = $action->getArgument("action_appl_id");
    
    if ($id == "") {
        $ActionCour = new Action($action->dbaccess);
    } else {
        $ActionCour = new Action($action->dbaccess, array(
            $id,
            $appl_id
        ));
    }
    
    $ActionCour->available = $action->getArgument("available");
    $err = "";
    if ($id == "") {
        $res = $ActionCour->Add();
        if ($res != "") {
            $err = _("err_add_action") . " : $res";
        }
    } else {
        $res = $ActionCour->Modify();
        if ($res != "") {
            $err = _("err_mod_action") . " : $res";
        }
    }
    $action->lay->template = json_encode(array(
        "success" => $err ? false : true,
        "error" => $err
    ));
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}
?>
