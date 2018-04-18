<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author  Anakeen
 * @version $Id: enum_choice.php,v 1.53 2009/01/08 17:48:27 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */


function enumjschoice(&$action)
{
    $sorm = GetHttpVars("sorm", "single"); // single or multiple
    $notalone = "true";

    if (preg_match("/([a-z]*)-alone/", $sorm, $reg)) {
        $notalone = "false";
    }
    $action->lay->set("notalone", $notalone);
}

/**
 * @param string $n
 * @param string $def
 * @param bool $whttpvars
 * @param \Anakeen\Core\Internal\SmartElement $doc
 * @param \Anakeen\Core\SmartStructure\NormalAttribute $oa
 *
 * @deprecated see \Dcp\Core\AutocompleteLib
 * @return array|bool|string
 */
function getFuncVar($n, $def, $whttpvars, &$doc, &$oa)
{
    return \Dcp\Core\AutocompleteLib::getFuncVar($n, $def, $whttpvars, $doc, $oa);
}

/**
 * @deprecated see \Dcp\Core\AutocompleteLib
 */
function getResPhpFunc(
    \Anakeen\Core\Internal\SmartElement & $doc,
    \Anakeen\Core\SmartStructure\NormalAttribute & $oattr,
    &$rargids,
    &$tselect,
    &$tval,
    $whttpvars = true,
    $index = ""
) {
    return \Dcp\Core\AutocompleteLib::getResPhpFunc($doc, $oattr, $rargids, $tselect, $tval, $whttpvars = true, $index);
}

/**
 * @deprecated see \Dcp\Core\AutocompleteLib
 */
function getAttr($dbaccess, $aid)
{
    return \Dcp\Core\AutocompleteLib::getAttr($aid);
}
