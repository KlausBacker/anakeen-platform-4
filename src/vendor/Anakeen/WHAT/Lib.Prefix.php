<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Default install directory
 *
 * @author Anakeen
 * @package FDL
 * @subpackage CORE
 */
/**
 */
global $pubdir;

$pubdir = realpath(dirname(__DIR__.  '/../../../../'));

set_include_path(implode([
    $pubdir,
        $pubdir."/vendor/Anakeen",
        $pubdir."/vendor/Anakeen/WHAT",
        $pubdir."/Apps",

    ], PATH_SEPARATOR) . PATH_SEPARATOR . get_include_path());

ini_set("session.use_cookies", "0");
ini_set("session.name", "session");
@ini_set("session.use_trans_sid", "0");
ini_set("session.cache_limiter", "nocache");
ini_set("magic_quotes_gpc", "Off");
ini_set("default_charset", "UTF-8");
ini_set("pcre.backtrack_limit", max(ini_get("pcre.backtrack_limit"), 10000000));
//ini_set("error_reporting", ini_get("error_reporting") & ~E_NOTICE);
define("DEFAULT_PUBDIR", $pubdir);
define("PUBLIC_DIR", realpath(DEFAULT_PUBDIR."/public"));
// Maximum length of a filename (should match your system NAME_MAX constant)
define("MAX_FILENAME_LEN", 255);
session_save_path(DEFAULT_PUBDIR."/var/session");
