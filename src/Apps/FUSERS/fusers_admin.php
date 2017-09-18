<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * progress bar tool
 *
 * @author Anakeen
 * @version $Id: fusers_admin.php,v 1.2 2006/04/03 14:56:26 eric Exp $
 * @package FDL
 * @subpackage FUSERS
 */
/**
 */

function fusers_admin(&$action)
{
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
}
