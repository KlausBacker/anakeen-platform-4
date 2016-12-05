<?php
/**
 * provided by module @moduleName@ (version @version@-@release@)
 */

global $app_const;

$app_const = array(
    "VERSION" => "@version@-@release@",
    "CSV_SEPARATOR" => array(
        "val" => ";",
        "global" => "N",
        "user" => "Y",
        "descr" => "CSV separator",
        "kind" => "enum(;|,)"
    ) ,
    "CSV_ENCLOSURE" => array(
        "val" => '"',
        "global" => "N",
        "user" => "Y",
        "descr" => "CSV enclosure",
        "kind" => "enum('|\")"
    )
);
