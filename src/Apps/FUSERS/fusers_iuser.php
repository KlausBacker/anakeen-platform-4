<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Redirector for generic
 *
 * @author Anakeen
 * @version $Id: fusers_iuser.php,v 1.2 2006/04/06 16:48:02 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");

function fusers_iuser(&$action)
{
    $bar = uniqid(getTmpDir() . "/wbar");
    wbar(1, -1, "lancement", $bar);
    
    $cmd = getWshCmd();
    
    $cmd.= "--bar=$bar --api=usercard_iuser ";
    
    bgexec(array(
        $cmd
    ) , $result, $err);
    
    redirect($action, "CORE", "PROGRESSBAR&bar=$bar");
}

function fusers_igroup(&$action)
{
    $bar = uniqid(getTmpDir() . "/wbar");
    wbar(1, -1, "lancement", $bar);
    
    $cmd = getWshCmd();
    
    $cmd.= "--bar=$bar --api=accountRefreshGroup ";
    
    bgexec(array(
        $cmd
    ) , $result, $err);
    
    redirect($action, "CORE", "PROGRESSBAR&bar=$bar");
}
function fusers_ldapinit(&$action)
{
    $bar = uniqid(getTmpDir() . "/wbar");
    wbar(1, -1, "lancement", $bar);
    
    $cmd = getWshCmd();
    
    $cmd.= "--bar=$bar --api=usercard_ldapinit ";
    bgexec(array(
        $cmd
    ) , $result, $err);
    
    redirect($action, "CORE", "PROGRESSBAR&bar=$bar");
}
?>
