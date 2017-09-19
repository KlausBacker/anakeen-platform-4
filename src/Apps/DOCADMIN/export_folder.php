<?php
/*
 * @author Anakeen
 * @package FDL
 */

include_once ("FDL/exportfld.php");
function export_folder(Action $action)
{
    
    $exportInvisibleVisibilities = true;
    exportfld($action, 0, "", "", $exportInvisibleVisibilities);
}
