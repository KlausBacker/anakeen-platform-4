<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Edit parameters for actions
 *
 * @author Anakeen
 * @version $Id: action_edit.php,v 1.4 2005/07/08 15:29:51 eric Exp $
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// -----------------------------------
function action_edit(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $id = $action->getArgument("id");
    $ActionCour = null;
    if ($id == "") {
        $action->lay->Set("name", "");
        $action->lay->Set("short_name", "");
        $action->lay->Set("long_name", "");
        $action->lay->Set("acl", "");
        $action->lay->Set("root", "");
        $action->lay->Set("toc", "");
        $action->lay->Set("id", "");
        $action->lay->Set("TITRE", _("titlecreateaction"));
        $action->lay->Set("BUTTONTYPE", _("butcreate"));
        
        $action->lay->set("openaccess", "");
    } else {
        $ActionCour = new Action($action->dbaccess, $id);
        $action->lay->eSet("id", $id);
        $action->lay->Set("name", $ActionCour->name);
        $action->lay->Set("short_name", $action->text($ActionCour->short_name));
        $action->lay->Set("long_name", $action->text($ActionCour->long_name));
        $action->lay->Set("acl", $ActionCour->acl);
        $action->lay->Set("root", $ActionCour->root);
        $action->lay->Set("toc", $ActionCour->toc);
        $action->lay->Set("TITRE", _("titlemodifyaction"));
        $action->lay->Set("BUTTONTYPE", _("butmodify"));
        $action->lay->set("openaccess", $ActionCour->openaccess);
    }
    
    $tab = array(
        array(
            "available" => "Y"
        ) ,
        array(
            "available" => "N"
        )
    );
    $action->lay->set("select_available", $ActionCour->available);
    
    $action->lay->SetBlockData("SELECTAVAILABLE", $tab);
    unset($tab);
}
