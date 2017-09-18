<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// -----------------------------------
function app_mod(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $id = $action->getArgument("id");
    
    if ($id == "") {
        $AppCour = new Application($action->dbaccess);
    } else {
        $AppCour = new Application($action->dbaccess, $id);
    }
    $AppCour->displayable = $action->getArgument("displayable");
    $AppCour->available = $action->getArgument("available");
    
    $err = "";
    if ($id == "") {
        $res = $AppCour->Add();
        if ($res != "") {
            $err = _("err_add_application") . " : $res";
        }
    } else {
        $res = $AppCour->Modify();
        if ($res != "") {
            $err = _("err_mod_application") . " : $res";
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
