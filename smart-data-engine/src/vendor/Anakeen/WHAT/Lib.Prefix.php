<?php
/**
 * Default install directory
 */
global $pubdir;

$pubdir = realpath(dirname(__DIR__.  '/../../../../'));

set_include_path(implode(PATH_SEPARATOR, [
    $pubdir,
        $pubdir."/vendor/Anakeen",
        $pubdir."/vendor/Anakeen/WHAT",
        $pubdir."/Apps",

    ]) . PATH_SEPARATOR . get_include_path());

define("DEFAULT_PUBDIR", $pubdir);
define("PUBLIC_DIR", realpath(DEFAULT_PUBDIR."/public"));
// Maximum length of a filename (should match your system NAME_MAX constant)
define("MAX_FILENAME_LEN", 255);

if (php_sapi_name() !== 'cli') {
    ini_set("session.use_cookies", "0");
    ini_set("session.name", "session");
    ini_set("session.use_trans_sid", "0");
    ini_set("session.cache_limiter", "nocache");
    ini_set("session.gc_maxlifetime", "2147483647");
    session_save_path(DEFAULT_PUBDIR."/var/session");
}
ini_set("magic_quotes_gpc", "Off");
ini_set("default_charset", "UTF-8");
ini_set("pcre.backtrack_limit", max(ini_get("pcre.backtrack_limit"), 10000000));
