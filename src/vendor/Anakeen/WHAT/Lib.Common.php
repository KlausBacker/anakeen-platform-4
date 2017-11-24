<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Common util functions
 *
 * @author Anakeen
 * @version $Id: Lib.Common.php,v 1.50 2008/09/11 14:50:04 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */
include_once ("Lib.Prefix.php");

function N_($s)
{
    return ($s);
}
if (!function_exists('pgettext')) {
    function pgettext($context, $msgid)
    {
        $contextString = "{$context}\004{$msgid}";
        $translation = _($contextString);
        if ($translation === $contextString) return $msgid;
        else return $translation;
    }
    
    function npgettext($context, $msgid, $msgid_plural, $num)
    {
        $contextString = "{$context}\004{$msgid}";
        $contextStringp = "{$context}\004{$msgid_plural}";
        $translation = ngettext($contextString, $contextStringp, $num);
        if ($translation === $contextString) {
            return $msgid;
        } else if ($translation === $contextStringp) {
            return $msgid_plural;
        } else {
            return $translation;
        }
    }
}
// New gettext keyword for regular strings with optional context argument
function ___($message, $context = "")
{
    if ($context != "") {
        return pgettext($context, $message);
    } else {
        return _($message);
    }
}
// New gettext keyword for plural strings with optional context argument
function n___($message, $message_plural, $num, $context = "")
{
    if ($context != "") {
        return npgettext($context, $message, $message_plural, abs($num));
    } else {
        return ngettext($message, $message_plural, abs($num));
    }
}
// to tag gettext without change text immediatly
// library of utilies functions
function print_r2($z, $ret = false)
{
    print "<PRE>";
    print_r($z, $ret);
    print "</PRE>\n";
    flush();
}
/**
 * send a message to system log
 * @param string $msg message to log
 * @param int $cut size limit
 */
function AddLogMsg($msg, $cut = 80)
{
    global $action;
    if (isset($action->parent)) $action->parent->AddLogMsg($msg, $cut);
}
/**
 * send a message to system log
 * @param string $msg
 */
function deprecatedFunction($msg = '')
{
    global $action;
    if (isset($action->parent)) $action->parent->log->deprecated("Deprecated : " . $msg);
}
/**
 * send a warning msg to the user interface
 * @param string $msg
 */
function addWarningMsg($msg)
{
    global $action;
    if (isset($action->parent)) $action->parent->addWarningMsg($msg);
}
/**
 * like ucfirst for utf-8
 * @param $s
 * @return string
 */
function mb_ucfirst($s)
{
    if ($s) {
        $s = mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8') , 'UTF-8') . mb_substr($s, 1, mb_strlen($s) , 'UTF-8');
    }
    return $s;
}

function mb_trim($string)
{
    return preg_replace("/(^\s+)|(\s+$)/us", "", $string);
}
/**
 * increase limit if current limit is lesser than
 * @param int $limit new limit in seconds
 */
function setMaxExecutionTimeTo($limit)
{
    $im = intval(ini_get("max_execution_time"));
    if ($im > 0 && $im < $limit && $limit >= 0) ini_set("max_execution_time", $limit);
    if ($limit <= 0) ini_set("max_execution_time", 0);
}
/**
 * get mail addr of a user
 * @param int $userid system user identifier
 * @param bool $full if true email is like : "John Doe" <John.doe@blackhole.net> else only system email address : john.doe@blackhole.net
 * @return string mail address, false if user not exists
 */
function getMailAddr($userid, $full = false)
{
    $user = new Account("", $userid);
    
    if ($user->isAffected()) {
        $pren = $postn = "";
        if ($full) {
            $pren = '"' . trim(str_replace('"', '-', ucwords(strtolower($user->getDisplayName($user->id))))) . '" <';
            $postn = '>';
        }
        return $pren . $user->getMail() . $postn;
    }
    return false;
}

function getTmpDir($def = '/tmp')
{
    static $tmp;
    if (isset($tmp) && !empty($tmp)) {
        return $tmp;
    }
    $tmp = \Dcp\Core\ContextManager::getApplicationParam('CORE_TMPDIR', $def);
    if (empty($tmp)) {
        if (empty($def)) {
            $tmp = './var/tmp';
        } else {
            $tmp = $def;
        }
    }
    
    if (substr($tmp, 0, 1) != '/') {
        $tmp = DEFAULT_PUBDIR . '/' . $tmp;
    }
    /* Try to create the directory if it does not exists */
    if (!is_dir($tmp)) {
        mkdir($tmp);
    }
    /* Add suffix, and try to create the sub-directory */
    $tmp = $tmp . '/dcp';
    if (!is_dir($tmp)) {
        mkdir($tmp);
    }
    /* We ignore any failure in the directory creation
     * and return the expected tmp dir.
     * The caller will have to handle subsequent
     * errors...
    */
    return $tmp;
}
/**
 * return value of parameters
 *
 * @deprecated  use \Dcp\Core\ContextManager::getApplicationParam
 * @see \Dcp\Core\ContextManager::getApplicationParam
 * @brief must be in core or global type
 * @param string $name param name
 * @param string $def default value if value is empty
 *
 * @return string
 */
function getParam($name, $def = "")
{
    return \Dcp\Core\ContextManager::getApplicationParam($name, $def );
}
/**
 * return value of a parameter
 *
 * @deprecated use Dcp\Core\ContextManager::getCoreParam
 * @brief must be in core or global type
 * @param string $name param name
 * @param string $def default value if value is empty
 *
 * @return string
 */
function getCoreParam($name, $def = "")
{
    return \Dcp\Core\ContextManager::getCoreParam($name, $def );
}
/**
 *
 * @param string $name the variable
 * @param string $def default value if variable is not defined
 * @return mixed
 */
function getSessionValue($name, $def = "")
{
    global $action;
    if ($action) return $action->read($name, $def);
    return null;
}
/**
 * return current log in user
 * @deprecated use Dcp\Core\ContextManager::getCurrentUser
 * @return Account
 */
function getCurrentUser()
{
    return \Dcp\Core\ContextManager::getCurrentUser();
}
function getLayoutFile($app, $layfile)
{
    global $action;
    if (strstr($layfile, '..')) {
        return "";
    }
    if (!strstr($layfile, '.')) $layfile.= ".xml";
    $socStyle = \Dcp\Core\ContextManager::getApplicationParam("CORE_SOCSTYLE");
    $style = \Dcp\Core\ContextManager::getApplicationParam("STYLE");
    $appDir = $action->parent->rootdir;

    if ($socStyle != "") {
        $file = $appDir . "/STYLE/$socStyle/Layout/$layfile";
        if (file_exists($file)) {
            return ($file);
        }
        
        $file = $appDir . "/STYLE/$socStyle/Layout/" . strtolower($layfile);
        if (file_exists($file)) {
            return ($file);
        }
    } elseif ($style != "") {
        $file = $appDir . "/STYLE/$style/Layout/$layfile";
        if (file_exists($file)) {
            return ($file);
        }
        
        $file = $appDir . "/STYLE/$style/Layout/" . strtolower($layfile);
        if (file_exists($file)) {
            return ($file);
        }
    }
    
    $file = $appDir . "/$app/Layout/$layfile";
    if (file_exists($file)) {
        return ($file);
    }
    
    $file = $appDir . "/$app/Layout/" . strtolower($layfile);
    if (file_exists($file)) {
        return ($file);
    }

    throw new Exception(sprintf("Cannot find Layout \"%s:%s\"", $app, $layfile));
}

function microtime_diff($a, $b)
{
    list($a_micro, $a_int) = explode(' ', $a);
    list($b_micro, $b_int) = explode(' ', $b);
    if ($a_int > $b_int) {
        return ($a_int - $b_int) + ($a_micro - $b_micro);
    } elseif ($a_int == $b_int) {
        if ($a_micro > $b_micro) {
            return ($a_int - $b_int) + ($a_micro - $b_micro);
        } elseif ($a_micro < $b_micro) {
            return ($b_int - $a_int) + ($b_micro - $a_micro);
        } else {
            return 0;
        }
    } else { // $a_int<$b_int
        return ($b_int - $a_int) + ($b_micro - $a_micro);
    }
}
/**
 * return call stack
 * @param int $slice last call to not return
 * @return array
 */
function getDebugStack($slice = 1)
{
    $td = @debug_backtrace(false);
    if (!is_array($td)) return array();
    $t = array_slice($td, $slice);
    foreach ($t as $k => $s) {
        unset($t[$k]["args"]); // no set arg
        
    }
    return $t;
}
/**
 * @param int $slice
 * @return void
 */
function logDebugStack($slice = 1)
{
    $st = getDebugStack(2);
    foreach ($st as $k => $t) {
        error_log(sprintf('%d) %s:%s %s::%s()', $k, isset($t["file"]) ? $t["file"] : 'closure', isset($t["line"]) ? $t["line"] : 0, isset($t["class"]) ? $t["class"] : '', $t["function"]));
    }
}


/**
 * @deprecated use Dcp\Core\DbManager::getDbid()
 * @return null|string
 */
function getDbid($dbaccess)
{
    return \Dcp\Core\DbManager::getDbid();
}

/**
 * @deprecated use Dcp\Core\DbManager::getDbAccess()
 * @return null|string
 */
function getDbAccess()
{
    return  \Dcp\Core\DbManager::getDbAccess();
}

function getDbAccessCore()
{
    return "service='" . getServiceCore() . "'";
}

function getDbAccessFreedom()
{
    return "service='" . getServiceFreedom() . "'";
}

function getServiceCore()
{
    static $pg_service = null;
    
    if ($pg_service) return $pg_service;
    $pgservice_core = getDbAccessvalue('pgservice_core');
    
    if ($pgservice_core == "") {
        error_log("Undefined pgservice_core in dbaccess.php");
        exit(1);
    }
    $pg_service = $pgservice_core;
    return $pg_service;
}
/**
 * return variable from dbaccess.php
 * @param string $varName
 * @return string|null
 * @throws Dcp\Exception
 */
function getDbAccessValue($varName)
{
    $included = false;
    $filename = sprintf("%s/config/dbaccess.php", DEFAULT_PUBDIR);
    if (file_exists($filename)) {
        if (include ($filename)) {
            $included = true;
        }
    }
    if (!$included) {
        fprintf(STDERR,  "Error: %s", $filename);
        throw new Dcp\Exception("FILE0005", $filename);
    }
    
    if (!isset($$varName)) return null;
    return $$varName;
}
function getServiceFreedom()
{
    static $pg_service = null;
    
    if ($pg_service) return $pg_service;
    $pgservice_freedom = getDbAccessValue('pgservice_freedom');
    if ($pgservice_freedom == "") {
        error_log("Undefined pgservice_freedom in dbaccess.php");
        exit(1);
    }
    $pg_service = $pgservice_freedom;
    return $pg_service;
}

function getDbName($dbaccess)
{
    error_log("Deprecated call to getDbName(dbaccess) : use getServiceName(dbaccess)");
    return getServiceName($dbaccess);
}

function getServiceName($dbaccess)
{
    if (preg_match("/service='?([a-zA-Z0-9_.-]+)/", $dbaccess, $reg)) {
        return $reg[1];
    }
    return '';
}
/**
 * send simple query to database
 * @deprecated use \Dcp\Core\DbManager::query
 * @param string $dbaccess access database coordonates (not used)
 * @param string $query sql query
 * @param string|bool|array &$result  query result
 * @param bool $singlecolumn  set to true if only one field is return
 * @param bool $singleresult  set to true is only one row is expected (return the first row). If is combined with singlecolumn return the value not an array, if no results and $singlecolumn is true then $results is false
 * @param bool $useStrict set to true to force exception or false to force no exception, if null use global parameter
 * @throws Dcp\Db\Exception
 * @return string error message. Empty message if no errors (when strict mode is not enable)
 */
function simpleQuery($dbaccess, $query, &$result = array() , $singlecolumn = false, $singleresult = false, $useStrict = null)
{
    static $sqlStrict = null;
    try {
        \Dcp\Core\DbManager::query($query, $result , $singlecolumn, $singleresult );
    } catch (\Dcp\Db\Exception $e) {
        if ($useStrict !== false) {
            if ($sqlStrict === null) {
                $sqlStrict = (\Dcp\Core\ContextManager::getApplicationParam("CORE_SQLSTRICT") !== "no");
            }
            if ($useStrict === true || $sqlStrict) {
                throw $e;
            }
        }
            return $e->getMessage();
    }
    return "";
}
/**
 * @param string $freedomctx
 * @deprecated
 * @return string
 */
function getAuthType($freedomctx = "")
{
    return AuthenticatorManager::getAuthType();
}
/**
 * @param string $freedomctx
 *
 * @deprecated
 * @return string
 */
function getAuthProvider($freedomctx = "")
{
    return AuthenticatorManager::getAuthProvider();
}
/**
 * @param string $freedomctx
 * @deprecated
 * @return array
 */
function getAuthProviderList($freedomctx = "")
{
    return AuthenticatorManager::getAuthProviderList();
}
/**
 * @deprecated
 * @param string $freedomctx
 *
 * @return array|mixed
 * @throws \Dcp\Exception
 */
function getAuthTypeParams($freedomctx = "")
{
    return Authenticator::getAuthTypeParams();
}
/**
 * @deprecated
 */
function getAuthParam($freedomctx = "", $provider = "")
{
    return Authenticator::getAuthParam($provider);
}
/**
 * return shell commande for wsh
 * depending of database (in case of several instances)
 * @param bool $nice set to true if want nice mode
 * @param int $userid the user identifier to send command (if 0 send like admin without specific user parameter)
 * @param bool $sudo set to true if want to be send with sudo (need /etc/sudoers correctly configured)
 * @return string the command
 */
function getWshCmd($nice = false, $userid = 0, $sudo = false)
{
    $wsh = '';
    if ($nice) $wsh.= "nice -n +10 ";
    if ($sudo) $wsh.= "sudo ";
    $wsh.= escapeshellarg(DEFAULT_PUBDIR) . "/wsh.php  ";
    $userid = intval($userid);
    if ($userid > 0) $wsh.= "--userid=$userid ";
    return $wsh;
}
/**
 * get the system user id
 * @return int
 */
function getUserId()
{
    $u=\Dcp\Core\ContextManager::getCurrentUser();
    if ($u) {
        return $u->id;
    }
    
    return 0;
}
/**
 * exec list of unix command in background
 * @param array $tcmd unix command strings
 * @param $result
 * @param $err
 */
function bgexec($tcmd, &$result, &$err)
{
    $foutname = uniqid(getTmpDir() . "/bgexec");
    $fout = fopen($foutname, "w+");
    fwrite($fout, "#!/bin/bash\n");
    foreach ($tcmd as $v) {
        fwrite($fout, "$v\n");
    }
    fclose($fout);
    chmod($foutname, 0700);
    //  if (session_id()) session_write_close(); // necessary to close if not background cmd
    exec("exec nohup $foutname > /dev/null 2>&1 &", $result, $err);
    //if (session_id()) @session_start();
    
}

function wbartext($text)
{
    wbar('-', '-', $text);
}

function wbar($reste, $total, $text = "", $fbar = false)
{
    static $preste, $ptotal;
    if (!$fbar) $fbar = GetHttpVars("bar"); // for progress bar
    if ($fbar) {
        if ($reste === '-') $reste = $preste;
        else $preste = $reste;
        if ($total === '-') $total = $ptotal;
        else $ptotal = $total;
        if (file_exists("$fbar.lck")) {
            $wmode = "w";
            unlink("$fbar.lck");
        } else {
            $wmode = "a";
        }
        $ffbar = fopen($fbar, $wmode);
        fputs($ffbar, "$reste/$total/$text\n");
        fclose($ffbar);
    }
}

function getJsVersion()
{
    include_once ("Class.QueryDb.php");
    $q = new QueryDb("", "param");
    $q->AddQuery("name='WVERSION'");
    $l = $q->Query(0, 0, "TABLE");
    $nv = 0;
    foreach ($l as $k => $v) {
        $nv+= intval(str_replace('.', '', $v["val"]));
    }
    
    return $nv;
}
/**
 * produce an anchor mailto '<a ...>'
 * @param string $to a valid mail address or list separated by comma -supported by client-
 * @param string $acontent
 * @param string $subject
 * @param string $cc
 * @param string $bcc
 * @param string $from
 * @param array $anchorattr
 * @param string $forcelink
 * @internal param string $anchor content <a...>anchor content</a>
 * @internal param array $treated as html anchor attribute : key is attribute name and value.. value
 * @internal param string $force link to be produced according the value
 * @return string like user admin dbname anakeen
 */
function setMailtoAnchor($to, $acontent = "", $subject = "", $cc = "", $bcc = "", $from = "", $anchorattr = array() , $forcelink = "")
{
    
    global $action;
    
    if ($to == "") return '';
    $classcode = '';
    if ($forcelink == "mailto") {
        $target = $forcelink;
    } else {
        $target = strtolower(\Dcp\Core\ContextManager::getApplicationParam("CORE_MAIL_LINK", "optimal"));
        if ($target == "optimal") {
            $target = "mailto";
        }
    }
    $prot = ($_SERVER["HTTPS"] == "on" ? "https" : "http");
    $host = $_SERVER["SERVER_NAME"];
    $port = $_SERVER["SERVER_PORT"];
    
    $attrcode = "";
    if (is_array($anchorattr)) {
        foreach ($anchorattr as $k => $v) $attrcode.= ' ' . $k . '="' . $v . '"';
    }
    
    $subject = str_replace(" ", "%20", $subject);
    
    switch ($target) {
        case "mailto":
            $link = '<a ';
            $link.= 'href="mailto:' . $to . '"';
            $link.= ($subject != "" ? '&Subject=' . $subject : '');
            $link.= ($cc != "" ? '&cc=' . $cc : '');
            $link.= ($bcc != "" ? '&bcc=' . $bcc : '');
            $link.= '"';
            $link.= $attrcode;
            $link.= '>';
            $link.= $acontent;
            $link.= '</a>';
            break;

        default:
            $link = '<span ' . $classcode . '>' . $acontent . '</span>';
    }
    return $link;
}
/**
 * Returns <kbd>true</kbd> if the string or array of string is encoded in UTF8.
 *
 * Example of use. If you want to know if a file is saved in UTF8 format :
 * <code> $array = file('one file.txt');
 * $isUTF8 = isUTF8($array);
 * if (!$isUTF8) --> we need to apply utf8_encode() to be in UTF8
 * else --> we are in UTF8 :)
 * </code>
 * @param mixed $string, or an array from a file() function.
 * @return boolean
 */
function isUTF8($string)
{
    if (is_array($string)) return seems_utf8(implode('', $string));
    else return seems_utf8($string);
}
/**
 * Returns <kbd>true</kbd> if the string  is encoded in UTF8.
 *
 * @param mixed $Str string
 * @return boolean
 */
function seems_utf8($Str)
{
    return preg_match('!!u', $Str);
}
/**
 * Initialise WHAT : set global $action whithout an authorized user
 * @deprecated use ContextManager::initContext
 *
 */
function WhatInitialisation($session = null)
{
    global $action;
    include_once ('Class.User.php');
    include_once ('Class.Session.php');
    
    $CoreNull = "";
    $core = new Application();
    $core->Set("CORE", $CoreNull, $session);
    if (!$session) {
        $core->session = new Session();
    }
    $action = new Action();
    $action->Set("", $core);
    // i18n
    $lang = $action->Getparam("CORE_LANG");
    \Dcp\Core\ContextManager::setLanguage($lang);
}

/**
 * @deprecated use ContextManager::sudo
 * @param $login
 */
function setSystemLogin($login)
{
    global $action;
    include_once ('Class.User.php');
    include_once ('Class.Session.php');
    
    if ($login != "") {
        $action->user = new Account(); //create user
        $action->user->setLoginName($login);
    }
}
/**
 * Returns a random password of specified length composed
 * with chars from the given charspace string or pattern
 */

function mkpasswd($length = 8, $charspace = "")
{
    if ($charspace == "") {
        $charspace = "[:alnum:]";
    }
    // Repeat a pattern e.g. [:a:3] -> [:a:][:a:][:a:]
    $charspace = preg_replace_callback('/(\[:[a-z]+:)(\d+)(\])/', function ($matches)
    {
        return str_repeat($matches[1] . $matches[3], $matches[2]);
    }
    , $charspace);
    // Expand [:patterns:]
    $charspace = preg_replace(array(
        "/\[:alnum:\]/",
        "/\[:extrastrong:\]/",
        "/\[:hex:\]/",
        "/\[:lower:\]/",
        "/\[:upper:\]/",
        "/\[:digit:\]/",
        "/\[:extra:\]/",
    ) , array(
        "[:lower:][:upper:][:digit:]",
        "[:extra:],;:=+*/(){}[]&@#!?\"'<>",
        "[:digit:]abcdef",
        "abcdefghijklmnopqrstuvwxyz",
        "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
        "0123456789",
        "-_.",
    ) , $charspace);
    
    $passwd = "";
    for ($i = 0; $i < $length; $i++) {
        $passwd.= substr($charspace, rand(0, strlen($charspace) - 1) , 1);
    }
    
    return $passwd;
}
/**
 * return lcdate use in database : 'iso'
 * Note: old 'dmy' format is not used since 3.2.8
 * @return string 'iso'
 */
function getLcdate()
{
    return 'iso';
}
/**
 *
 * @param string $core_lang
 * @deprecated use Dcp\Core\ContextManager::getLocaleConfig
 * @return bool|array
 */
function getLocaleConfig($core_lang = '')
{
    return \Dcp\Core\ContextManager::getLocaleConfig($core_lang);
}

function getLocales()
{
    static $locales = null;
    
    if ($locales === null) {
        $lang = array();
        include ('Apps/CORE/lang.php');
        $locales = $lang;
    }
    return $locales;
}
/**
 * use new locale language
 * @deprecated use Dcp\Core\ContextManager::setLanguage
 * @param string $lang like fr_FR, en_US
 * @throws \Dcp\Core\Exception
 */
function setLanguage($lang)
{
    \Dcp\Core\ContextManager::setLanguage($lang);
}
// use UTF-8 by default
mb_internal_encoding('UTF-8');
