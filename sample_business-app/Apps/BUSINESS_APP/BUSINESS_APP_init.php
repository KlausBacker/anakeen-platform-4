<?php

/**
 * provided by module @moduleName@ (version @version@-@release@)
 */

global $app_const;


$initialConfig= [ "collections" => []];

$app_const = array(
    "VERSION" => "@version@-@release@",
    "CONFIG" => [
        "val" => json_encode($initialConfig),
        "descr" => ("Collection config") ,
        "kind" => "text"
    ]
);
