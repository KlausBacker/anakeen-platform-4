<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: app_delete.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// -----------------------------------
function app_delete(Action & $action)
{
    // -----------------------------------
    $appsel = $action->getArgument("appsel");
    
    $application = new Application("", $appsel);
    $action->log->info("Remove " . $application->name);
    $err = $application->DeleteApp();
    $action->lay->template = json_encode(array(
        "success" => $err ? false : true,
        "error" => $err
    ));
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}
?>
