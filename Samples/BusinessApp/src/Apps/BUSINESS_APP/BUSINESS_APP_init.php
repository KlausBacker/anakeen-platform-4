<?php

/**
 * provided by module @moduleName@ (version @version@-@release@)
 */

global $app_const;


$initialConfig = [
    "collections" => [
        [
            "ref"=>"BA_CLIENT",
            "initid"=>"BABA_CLIENT",
            "image_url"=>"api/v1/images/assets/sizes/24x24c/BA_Client.png",
            "html_label"=>"Dossier client"
        ],
        [
            "ref"=>"BA_CERTIFICATION",
            "initid"=>"BABA_CERTIFICATION",
            "image_url"=>"api/v1/images/assets/sizes/24x24c/BA_Certification.png",
            "html_label"=>"Dossier de certification"
        ],
        [
            "ref"=>"BA_PROSPECT",
            "initid"=>"BABA_PROSPECT",
            "image_url"=>"api/v1/images/assets/sizes/24x24c/BA_Prospect.png",
            "html_label"=>"Prospect"
        ]
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
