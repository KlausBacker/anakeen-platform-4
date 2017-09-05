<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * @author Anakeen
 * @package FDL
 */
// ---------------------------------------------------------------
// $Id: Lib.VaultCommon.php,v 1.7 2007/03/07 18:43:26 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Lib.VaultCommon.php,v $
// ---------------------------------------------------------------
// ---------------------------------------------------------
function fileextension($filename, $ext = "nop")
{
    $te = explode(".", basename($filename));
    if (count($te) > 1) $ext = $te[count($te) - 1];
    return $ext;
}
// ---------------------------------------------------------
function vaultfilename($fspath, $name, $id)
{
    return str_replace('//', '/', $fspath . "/" . $id . "." . fileextension($name));
}

function my_basename($p)
{
    //return basename($p);
    $r = strrpos($p, "/");
    return ($r !== false) ? substr($p, $r + 1) : $p;
}
?>