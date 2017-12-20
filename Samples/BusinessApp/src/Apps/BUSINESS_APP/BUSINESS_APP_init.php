<?php

/**
 * provided by module @moduleName@ (version @version@-@release@)
 */

global $app_const;


$initialConfig = [
    "showcase_families" => ["BA_CLIENT_CONTRACT", "BA_PROVIDER_CONTRACT", "BA_FEES", "BA_RH_DIR"]
];

$app_const = array(
    "VERSION" => "@version@-@release@",
    "SAMPLE_CONFIG" => [
        "val" => json_encode($initialConfig, JSON_UNESCAPED_SLASHES),
        "descr" => ("Collections config"),
        "kind" => "text"
    ]
);
