<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * @author Anakeen
 */
// ---------------------------------------------------------------
// $Id: FUSERS_init.php,v 1.2 2005/10/27 14:38:15 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Fusers/FUSERS_init.php,v $
// ---------------------------------------------------------------
global $app_const;

$app_const = array(
    "INIT" => "yes",
    "VERSION" => "3.2.11-0",
    
    "FUSERS_MAINLINE" => array(
        "kind" => "static",
        "val" => "25",
        "descr" => N_("main view line displayed") ,
        "user" => "Y"
    ) ,
    "FUSERS_MAINCOLS" => array(
        "kind" => "static",
        "val" => "",
        "descr" => N_("main view columns") ,
        "user" => "Y"
    ) ,
    "FUSERS_DISPLAYLENGTH" => array(
        "val" => "10",
        "descr" => N_("datatable displaylength") ,
        "user" => "Y"
    ),
    "FUSERS_GTREESTATE" => array(
        "kind" => "text",
        "val" => "",
        "desc" => N_("Group's tree state") ,
        "user" => "Y"
    ),
    "FUSERS_PORTPREF" => array(
    "kind" => "text",
    "val" => "",
    "desc" => N_("Account import/export preferences") ,
    "user" => "Y"
)
);
