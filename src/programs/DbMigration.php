#!/usr/bin/env php
<?php
/**
 * apply migration script
 *
 */


require __DIR__ . '/../vendor/Anakeen/autoload.php';
require __DIR__ . "/../vendor/Anakeen/WHAT/Lib.Prefix.php";


$longopts = array(
    "file:", // Valeur requise
    "verbose::", // Valeur optionnelle
    "dry-run", //
    "help"
);
$options = getopt("", $longopts);
if (isset($options["help"])) {
    print migrUsage($argv);
    return 0;
}
$om = new \Anakeen\Database\DbMigration($options["file"]);
$om->verbose(isset($options["verbose"]) ? (empty($options["verbose"]) ? 1 : $options["verbose"]) : 0);
if (isset($options['dry-run'])) {
    $om->dryRun();
}
$om->execute();

function migrUsage($argv)
{
    return sprintf(
        "Apply migration rules\n".
        "%s\n" .
        "\t--file    : xml file of migration rules\n" .
        "\t--verbose : verbose level 0 to 3 [default 0]\n" .
        "\t--dry-run : SQL queries are rollback at the end or if error occurs\n",
        $argv[0]
    );
}
