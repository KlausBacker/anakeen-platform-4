#!/usr/bin/env php
<?php
/**
 * Concatenate '*catalog.js' (by lang) JSON localization files
 *
 * @author Anakeen
 */

$rootDir = realpath(__DIR__ . "/..");
$localeData = [];

$localeDir = "$rootDir/public/locale";
foreach (glob("$localeDir/*/catalog.json") as $filename) {
    $dirLang = basename(dirname($filename));
    require("$rootDir/locale/$dirLang/lang.php");
    $localeIndex = "";
    foreach ($lang as $langIndex => $langData) {
        if ($langData["locale"] === $dirLang) {
            $localeIndex = $langData["culture"];
        }
    }
    if ($localeIndex) {
        $localeData[$localeIndex] = json_decode(file_get_contents($filename), true);
    }
}

file_put_contents("$localeDir/catalog.json", json_encode($localeData));
