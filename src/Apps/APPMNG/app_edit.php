<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: app_edit.php,v 1.6 2005/07/08 15:29:51 eric Exp $
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// -----------------------------------
function app_edit(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $id = $action->getArgument("id");
    $AppCour = null;
    if ($id == "") {
        $action->lay->Set("name", "");
        $action->lay->Set("short_name", "");
        $action->lay->Set("description", "");
        $action->lay->Set("passwd", "");
        $action->lay->Set("id", "");
        $action->lay->Set("TITRE", _("titlecreate"));
        $action->lay->Set("BUTTONTYPE", _("butcreate"));
    } else {
        $AppCour = new Application($action->dbaccess, $id);
        $action->lay->eSet("id", $id);
        $action->lay->Set("name", $AppCour->name);
        $action->lay->Set("short_name", _($AppCour->short_name));
        $action->lay->Set("description", _($AppCour->description));
        $action->lay->Set("passwd", "");
        $action->lay->Set("TITRE", _("titlemodify"));
        $action->lay->Set("BUTTONTYPE", _("butmodify"));
    }
    
    $action->lay->set("selected_available", $AppCour->available);
    $tab = array(
        array(
            "available" => "Y"
        ) ,
        array(
            "available" => "N"
        )
    );
    
    $action->lay->SetBlockData("SELECTAVAILABLE", $tab);
    
    $action->lay->set("selected_displayable", $AppCour->displayable);
    $tab = array(
        array(
            "displayable" => "Y"
        ) ,
        array(
            "displayable" => "N"
        )
    );
    
    $action->lay->SetBlockData("SELECTDISPLAYABLE", $tab);
}
