#!/usr/bin/env php
<?php
/**
 * detect pre migration script
 *
 */


require __DIR__ . '/../vendor/Anakeen/autoload.php';
require __DIR__ . "/../vendor/Anakeen/WHAT/Lib.Prefix.php";


$longopts = array(
    "file:", // Valeur requise
    "verbose::", // Valeur optionnelle
    "dry-run", //
    
);
$options = getopt("", $longopts);

$om = new \Anakeen\Database\DbMigration($options["file"]);
$om->verbose(isset($options["verbose"]) ? (empty($options["verbose"]) ? 1 : $options["verbose"]) : 0);
if (isset($options['dry-run'])) {
    $om->dryRun();
}
$om->execute();
