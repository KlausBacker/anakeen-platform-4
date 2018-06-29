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










