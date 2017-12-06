<?php

/**
 * provided by module @moduleName@ (version @version@-@release@)
 */

global $app_const;


$initialConfig = [
    "collections" => [
        [
            "ref"=>"BA_CLIENT_CONTRACT",
            "initid"=>"BA_CLIENT_CONTRACT",
            "image_url"=>"api/v1/images/assets/original/BA_Client.png",
            "html_label"=>"Contrat Client"
        ],
        [
            "ref"=>"BA_PROVIDER_CONTRACT",
            "initid"=>"BA_PROVIDER_CONTRACT",
            "image_url"=>"api/v1/images/assets/original/BA_Provider.png",
            "html_label"=>"Contrat Fournisseur"
        ],
        [
            "ref"=>"BA_FEES",
            "initid"=>"BA_FEES",
            "image_url"=>"api/v1/images/assets/original/BA_Fees.png",
            "html_label"=>"Note de frais"
        ],
        [
            "ref"=>"BA_RH_DIR",
            "initid"=>"BA_RH_DIR",
            "image_url"=>"api/v1/images/assets/original/BA_RHDir.png",
            "html_label"=>"Dossier RH"
        ],
    ]
];

$app_const = array(
    "VERSION" => "@version@-@release@",
    "SAMPLE_CONFIG" => [
        "val" => json_encode($initialConfig, JSON_UNESCAPED_SLASHES),
        "descr" => ("Collections config"),
        "kind" => "text"
    ]
);
