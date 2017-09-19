<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_frame.php,v 1.4 2005/03/24 15:06:56 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
//
// ---------------------------------------------------------------
// -----------------------------------
function docs_admin(Action &$action)
{
    // -----------------------------------
    $mode = ApplicationParameterManager::getScopedParameterValue('FREEDOM_VIEWFRAME', "navigator");

    $action->lay->set('FOLDER_MODE', $mode == "folder");
    
    $dirid = GetHttpVars("dirid", 0); // root directory
    $action->lay->Set("dirid", urlencode($dirid));
}
