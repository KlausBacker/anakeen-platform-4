<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Common util functions
 *
 * @author     Anakeen
 * @version    $Id: Lib.Common.php,v 1.50 2008/09/11 14:50:04 eric Exp $
 * @package    FDL
 * @subpackage CORE
 */
/**
 */
require_once(__DIR__."/../FDL/LegacyDocManager.php");

function N_($s)
{
    return ($s);
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
 * @deprecated use \Anakeen\Core\Utils\System::addLogMsg
 * @param string $msg message to log
 * @param int    $cut size limit
 */
function AddLogMsg($msg, $cut = 80)
{
    \Anakeen\Core\Utils\System::addLogMsg($msg);
}



/**
 * send a warning msg to the user interface
 * @deprecated
 * @param string $msg
 */
function addWarningMsg($msg)
{
    global $action;
    if (isset($action->parent)) {
        $action->parent->addWarningMsg($msg);
    }
}

/**
 * like ucfirst for utf-8
 * @deprecated use Anakeen\Core\Utils\Strings::mb_ucfirst
 * @param $s
 *
 * @return string
 */
function mb_ucfirst($s)
{
    return Anakeen\Core\Utils\Strings::mb_ucfirst($s);
}

/**
 * @param $string
 * @deprecated use Anakeen\Core\Utils\Strings::mb_trim
 * @return null|string|string[]
 */
function mb_trim($string)
{
    return Anakeen\Core\Utils\Strings::mb_trim($string);
}



/**
 * get mail addr of a user
 *
 * @param int  $userid system user identifier
 * @param bool $full   if true email is like : "John Doe" <John.doe@blackhole.net> else only system email address : john.doe@blackhole.net
 *
 * @return string mail address, false if user not exists
 */
function getMailAddr($userid, $full = false)
{
    $user = new \Anakeen\Core\Account("", $userid);

    if (!$user->isAffected()) {
        return false;
    }
    $mailAddr = $user->getMail();
    if ($full && $mailAddr !== '') {
        /* Compose full address iif the user has a non-empty mail address */
        $pren = '"' . trim(str_replace('"', '-', ucwords(strtolower($user->getDisplayName($user->id))))) . '" <';
        $postn = '>';
        return $pren . $mailAddr . $postn;
    }
    return $mailAddr;
}

/**
 * @param string $def
 *
 * @deprecated use \Anakeen\Core\ContextManager::getTmpDir()
 * @return string
 */
function getTmpDir($def = '/tmp')
{
    return \Anakeen\Core\ContextManager::getTmpDir($def);
}

/**
 * return value of parameters
 *
 * @deprecated  use \Anakeen\Core\ContextManager::getApplicationParam
 * @see         \Anakeen\Core\ContextManager::getParameterValue
 * @brief       must be in core or global type
 *
 * @param string $name param name
 * @param string $def  default value if value is empty
 *
 * @return string
 */
function getParam($name, $def = "")
{
    return \Anakeen\Core\ContextManager::getParameterValue($name, $def);
}

/**
 * return value of a parameter
 *
 * @deprecated use Anakeen\Core\ContextManager::getCoreParam
 * @brief      must be in core or global type
 *
 * @param string $name param name
 * @param string $def  default value if value is empty
 *
 * @return string
 */
function getCoreParam($name, $def = "")
{
    return \Anakeen\Core\ContextManager::getCoreParam($name, $def);
}

/**
 *
 * @param string $name the variable
 * @param string $def  default value if variable is not defined
 *
 * @return mixed
 */
function getSessionValue($name, $def = "")
{
    global $action;
    if ($action) {
        return $action->read($name, $def);
    }
    return null;
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
 * @deprecated
 * @param int $slice last call to not return
 *
 * @return array
 */
function getDebugStack($slice = 1)
{
    $td = @debug_backtrace(false);
    if (!is_array($td)) {
        return array();
    }
    $t = array_slice($td, $slice);
    foreach ($t as $k => $s) {
        unset($t[$k]["args"]); // no set arg
    }
    return $t;
}

/**
 * @param int    $slice call stack offset
 * @param string $msg   Error message
 *
 * @return void
 */
function logDebugStack($slice = 1, $msg = "")
{
    $st = getDebugStack(2);
    $errors = [];
    if ($msg) {
        $errors[] = $msg;
    }
    foreach ($st as $k => $t) {
        $errors[] = sprintf(
            '%d) %s:%s %s::%s()',
            $k,
            isset($t["file"]) ? $t["file"] : 'closure',
            isset($t["line"]) ? $t["line"] : 0,
            isset($t["class"]) ? $t["class"] : '',
            $t["function"]
        );
    }

    error_log(implode("\n", $errors));
}






/**
 * return variable from dbaccess.php
 *
 * @param string $varName
 * @deprecated
 * @return string|null
 * @throws Dcp\Exception
 */
function getDbAccessValue($varName)
{
    $included = false;

    $filename = sprintf("%s/%s", DEFAULT_PUBDIR, \Anakeen\Core\Settings::DbAccessFilePath);
    if (file_exists($filename)) {
        if (include($filename)) {
            $included = true;
        }
    }
    if (!$included) {
        fprintf(STDERR, "Error: %s", $filename);
        throw new Dcp\Exception("FILE0005", $filename);
    }

    if (!isset($$varName)) {
        return null;
    }
    return $$varName;
}






/**
 * exec list of unix command in background
 * @deprecated
 * @param array $tcmd unix command strings
 * @param       $result
 * @param       $err
 */
function bgexec($tcmd, &$result, &$err)
{
    \Anakeen\Core\Utils\System::bgexec($tcmd, $result, $err);
}



function getJsVersion()
{
    $q = new \Anakeen\Core\Internal\QueryDb("", \Anakeen\Core\Internal\Param::class);
    $q->AddQuery("name='WVERSION'");
    $l = $q->Query(0, 0, "TABLE");
    $nv = 0;
    foreach ($l as $k => $v) {
        $nv += intval(str_replace('.', '', $v["val"]));
    }

    return $nv;
}

/**
 * produce an anchor mailto '<a ...>'
 *
 * @param string $to a valid mail address or list separated by comma -supported by client-
 * @param string $acontent
 * @param string $subject
 * @param string $cc
 * @param string $bcc
 * @param string $from
 * @param array  $anchorattr
 * @param string $forcelink
 *
 * @internal param string $anchor content <a...>anchor content</a>
 * @internal param array $treated as html anchor attribute : key is attribute name and value.. value
 * @internal param string $force link to be produced according the value
 * @return string like user admin dbname anakeen
 */
function setMailtoAnchor(
    $to,
    $acontent = "",
    $subject = "",
    $cc = "",
    $bcc = "",
    $from = "",
    $anchorattr = array(),
    $forcelink = ""
) {
    if ($to == "") {
        return '';
    }
    $classcode = '';
    if ($forcelink == "mailto") {
        $target = $forcelink;
    } else {
        $target = strtolower(\Anakeen\Core\ContextManager::getParameterValue("CORE_MAIL_LINK", "optimal"));
        if ($target == "optimal") {
            $target = "mailto";
        }
    }

    $attrcode = "";
    if (is_array($anchorattr)) {
        foreach ($anchorattr as $k => $v) {
            $attrcode .= ' ' . $k . '="' . $v . '"';
        }
    }

    $subject = str_replace(" ", "%20", $subject);

    switch ($target) {
        case "mailto":
            $link = '<a ';
            $link .= 'href="mailto:' . $to . '"';
            $link .= ($subject != "" ? '&Subject=' . $subject : '');
            $link .= ($cc != "" ? '&cc=' . $cc : '');
            $link .= ($bcc != "" ? '&bcc=' . $bcc : '');
            $link .= '"';
            $link .= $attrcode;
            $link .= '>';
            $link .= $acontent;
            $link .= '</a>';
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
 *
 * @param mixed $string , or an array from a file() function.
 * @deprecated use Anakeen\Core\Utils\Strings::isUTF8
 * @return boolean
 */
function isUTF8($string)
{
    return Anakeen\Core\Utils\Strings::isUTF8($string);
}

/**
 * Returns <kbd>true</kbd> if the string  is encoded in UTF8.
 * @deprecated use Anakeen\Core\Utils\Strings::seemsUTF8
 * @param mixed $Str string
 *
 * @return boolean
 */
function seems_utf8($Str)
{
    return Anakeen\Core\Utils\Strings::seemsUTF8($Str);
}

/**
 * Initialise WHAT : set global $action whithout an authorized user
 *
 * @deprecated use ContextManager::initContext
 *
 * @param \Anakeen\Core\Internal\Session $session
 *
 * @throws Exception
 * @throws \Dcp\Core\Exception
 * @throws \Dcp\Db\Exception
 */
function WhatInitialisation($session = null)
{
    global $action;

    $CoreNull = "";
    $core = new \Anakeen\Core\Internal\Application();
    $core->Set("CORE", $CoreNull, $session);
    if (!$session) {
        $core->session = new \Anakeen\Core\Internal\Session();
    }
    $action = new \Anakeen\Core\Internal\Action();
    $action->Set("", $core);
    // i18n
    $lang = $action->Getparam("CORE_LANG");
    \Anakeen\Core\ContextManager::setLanguage($lang);
}

/**
 * @deprecated use ContextManager::sudo
 *
 * @param $login
 */
function setSystemLogin($login)
{
    global $action;
    include_once('Class.User.php');
    include_once('Class.Session.php');

    if ($login != "") {
        $action->user = new \Anakeen\Core\Account(); //create user
        $action->user->setLoginName($login);
    }
}


/**
 * return lcdate use in database : 'iso'
 * Note: old 'dmy' format is not used since 3.2.8
 *
 * @return string 'iso'
 */
function getLcdate()
{
    return 'iso';
}

/**
 *
 * @param string $core_lang
 *
 * @deprecated use Anakeen\Core\ContextManager::getLocaleConfig
 * @return bool|array
 */
function getLocaleConfig($core_lang = '')
{
    return \Anakeen\Core\ContextManager::getLocaleConfig($core_lang);
}

/**
 * @return array|null
 * @deprecated
 */
function getLocales()
{
    static $locales = null;

    if ($locales === null) {
        $lang = array();
        include('Apps/CORE/lang.php');
        $locales = $lang;
    }
    return $locales;
}

/**
 * use new locale language
 *
 * @deprecated use Anakeen\Core\ContextManager::setLanguage
 *
 * @param string $lang like fr_FR, en_US
 *
 */
function setLanguage($lang)
{
    \Anakeen\Core\ContextManager::setLanguage($lang);
}

// use UTF-8 by default
mb_internal_encoding('UTF-8');
