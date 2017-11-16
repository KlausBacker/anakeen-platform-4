<?php

global $app_const;

$app_const = array(
    "INIT" => "yes",
    "VERSION" => "1.0.2-0",
    "TE_HOST" => array(
        "val" => "",
        "global" => "Y",
        "user" => "N",
        "descr" => N_("Transformation Engine server host name")
    ),
    "TE_PORT" => array(
        "val" => "",
        "global" => "Y",
        "user" => "N",
        "descr" => N_("Transformation Engine server port number")
    ),
    "TE_ACTIVATE" => array(
        "val" => "no",
        "global" => "Y",
        "user" => "N",
        "descr" => N_("Activate Transformation Engine"),
        "kind" => "enum(yes|no)"
    ),
    "TE_FULLTEXT" => array(
        "val" => "yes",
        "global" => "Y",
        "user" => "N",
        "descr" => N_("Activate file indexing with TE"),
        "kind" => "enum(yes|no)"
    ),
    "TE_URLINDEX" => array(
        "val" => "",
        "global" => "Y",
        "user" => "N",
        "descr" => N_("Transformation Engine callback url")
    ),
    "TE_TIMEOUT" => array(
        "val" => "2",
        "global" => "Y",
        "user" => "N",
        "descr" => N_("Transformation Engine timeout connection (in seconds)")
    )
);
