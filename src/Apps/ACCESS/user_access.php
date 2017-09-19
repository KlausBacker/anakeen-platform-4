<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: user_access.php,v 1.11 2007/02/16 08:32:08 eric Exp $
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: user_access.php,v 1.11 2007/02/16 08:32:08 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/user_access.php,v $
// ---------------------------------------------------------------
include_once ("FDL/editutil.php");
// -----------------------------------
function user_access(Action & $action, $accountType = "U")
{
    // -----------------------------------
    $filteruser = $action->getArgument("userfilter");
    
    $user_id = $action->getArgument("uid");
    $action->lay->eSet("userfilter", $filteruser);
    
    $packUrl = $action->parent->getJsLink("ACCESS:access.js", true, "USER_ACCESS");
    $action->parent->getJsLink("ACCESS:user_access.js", true, "USER_ACCESS");
    $action->parent->getJsLink("ACCESS/Layout/edit.js", false, "USER_ACCESS");
    
    $jslinks = array(
        array(
            "src" => $action->parent->getJsLink("legacy/jquery-ui/js/jquery.js")
        ) ,
        array(
            "src" => $action->parent->getJsLink("legacy/jquery-ui/js/jquery-ui.js")
        ) ,
        array(
            "src" => $action->parent->getJsLink("legacy/jquery-dataTables/js/jquery.dataTables.js")
        ) ,
        array(
            "src" => $packUrl
        )
    );
    
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    $action->parent->addCssRef("legacy/jquery-dataTables/css/jquery.dataTables.css");
    $action->parent->addCssRef("ACCESS/Layout/user_access.css");
    $action->parent->addCssRef("admin/adminAssets/adminReset.css");
    $action->parent->addCssRef("ACCESS/Layout/edit.css");
    
    $action->lay->setBlockData("JS_LINKS", $jslinks);
    $action->lay->set("idAURG", "iduser");
    // Set the edit form element
    $action->lay->eSet("shortname", _($action->text("access:appname")));
    $action->lay->eSet("desc", _($action->text("appdesc")));
    $action->lay->eSet("permission", $action->text("permissions"));
    $action->lay->eSet("placeholder", _("Application filter"));
    
    $action->lay->set("maxreach", false);
    $action->lay->set("usefilter", false);
    $action->lay->eSet("accountType", $accountType);
    $action->lay->set("URG", true);
    // affect the select form elements
    $u = new Account();
    if ($accountType == "G") {
        $action->lay->set("changeLabel", _("Select Group Access"));
    } elseif ($accountType == "R") {
        $action->lay->set("changeLabel", _("Select Role Access"));
    } else {
        $action->lay->set("changeLabel", _("Select User Access"));
    }
    // select the first user if not set
    if ($user_id == "") {
        simpleQuery($action->dbaccess, sprintf("select id from users where accounttype='%s' and id != 1 order by id limit 1", pg_escape_string($accountType)) , $user_id, true, true);
    }
    
    $u->select($user_id);
    if ($accountType == "U") {
        $value = trim(sprintf("%s %s (%s)", $u->lastname, $u->firstname, $u->login));
    } else {
        $value = $dn = trim(sprintf("%s %s", $u->lastname, $u->firstname));
    }
    $action->lay->eSet("valueAURG", $value);
    $action->lay->eSet("valueidAURG", $user_id);
    
    $action->lay->set("hasuser", $u->id ? true : false);
}
