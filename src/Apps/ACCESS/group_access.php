<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ("ACCESS/user_access.php");
function group_access(Action & $action)
{
    user_access($action, "G");
}
?>
