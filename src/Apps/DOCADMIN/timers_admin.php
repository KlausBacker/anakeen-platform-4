<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Timer management
 *
 * @author Anakeen
 * @version $Id: admin_timers.php,v 1.2 2009/01/02 17:43:50 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocTimer.php");
/**
 * Timers management
 * @param Action &$action current action
 */
function timers_admin(&$action)
{
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/AnchorPosition.js");
}
?>