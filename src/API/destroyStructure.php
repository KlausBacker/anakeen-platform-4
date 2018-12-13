<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Delete structure and elements
 *
 * @subpackage
 */

use Anakeen\Core\SmartStructure\DestroySmartStructure;

/**
 */


$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Delete smart structures and its elements");
$docid = $usage->addRequiredParameter("name", "structure logical name");
$force = $usage->addHiddenParameter("force", "force without transaction");

$transaction = $usage->addEmptyParameter("transaction", "abort deletion if one of query failed");
if (!$force) {
    $force = !$transaction;
} else {
    $force = ($force == "yes");
}
$usage->verify();


DestroySmartStructure::destroyFamily($docid, $force);
