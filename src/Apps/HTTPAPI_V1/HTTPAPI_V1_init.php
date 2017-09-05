<?php
/*
 * @author Anakeen
 * @package FDL
*/

global $app_const;

$json_system_logger = <<<'JSON'
[
    "\\Dcp\\HttpApi\\V1\\Logger\\Dcp"
]
JSON;

$app_const = array(
    "VERSION" => "1.0.3-0",
    "CRUD_CLASS" => array(
        "val" => "[]",
        "descr" => N_("HTTPAPI_V1:crud class"),
        "kind" => "static"
    ),
    "CRUD_MIDDLECLASS" => array(
        "val" => "[]",
        "descr" => N_("HTTPAPI_V1:middleware crud classes"),
        "kind" => "static"
    ),
    "SYSTEM_LOGGER" => array(
        "val" => $json_system_logger,
        "descr" => N_("HTTPAPI_V1:default logger class"),
        "kind" => "static"
    ),
    "CUSTOM_LOGGER" => array(
        "val" => "[]",
        "descr" => N_("HTTPAPI_V1:custom logger class")
    ),
    "DOCUMENTATION_URL" => array(
        "val" => "http://docs.anakeen.com/dynacase/3.2/dynacase-doc-httpapi/",
        "descr" => N_("HTTPAPI_V1:Anakeen documentation URL"),
        "kind" => "static"
    ),
    "REST_BASE_URL" => array(
        "val" => "api/v1/",
        "descr" => N_("HTTPAPI_V1:Base URL used to generate canonic URL response (uri)")
    ),
    "DEFAULT_PAGE" => array(
        "val" => "?app=HTTPAPI_V1",
        "descr" => N_("HTTPAPI_V1:API default page"),
        "kind" => "static"
    ),
    "ACTIVATE_CACHE" => array(
        "val" => "TRUE",
        "descr" => N_("HTTPAPI_V1:activate the etag cache for the request that handle it"),
        "kind" => "enum(TRUE|FALSE)"
    ),
    "ACTIVATE_TRACE" => array(
        "val" => "FALSE",
        "descr" => N_("HTTPAPI_V1:activate memory/time tracing"),
        "kind" => "static"
    ),
    "COLLECTION_DEFAULT_SLICE" => array(
        "val" => "10",
        "descr" => N_("HTTPAPI_V1:default value of the slice for collection")
    ),
);
