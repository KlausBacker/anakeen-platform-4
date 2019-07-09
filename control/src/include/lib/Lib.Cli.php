<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * CLI Library
 *
 * @author Anakeen
 */

require_once(__DIR__ . '/../class/Class.WIFF.php');

global $wiff_lock;
function printerr($msg)
{
    file_put_contents('php://stderr', $msg);
    $wiff = WIFF::getInstance();
    $wiff->log(LOG_ERR, $msg);
}


/**
 * wiff (un)lock
 *
 * @return void
 */
function wiff_lock()
{
    $wiff = WIFF::getInstance();
    if ($wiff->lock(false, $lockerPid) === false) {
        printerr(sprintf("Error locking Anakeen-Control: %s\n", $wiff->errorMessage));
        exit(100);
    }
}

function wiff_unlock()
{
    $wiff = WIFF::getInstance();
    $ret = $wiff->unlock();
    if ($ret === false) {
        printerr(sprintf("Warning: could not unlock Anakeen-Control!\n"));
    }
    return $ret;
}


function wiff_param_help()
{
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff param show\n";
    echo "\n";
    echo "  wiff param set <param-name> <param-value> ['hidden']\n";
    echo "\n";
    echo "  wiff param get <param-name>\n";
    echo "\n";
    return 0;
}


/**
 * wiff context <ctxName> shell
 *
 * @param Context $context
 * @param         $argv
 *
 * @return int
 */
function wiff_context_shell(&$context, &$argv)
{
    if (!function_exists("posix_setuid")) {
        throw new Exception(sprintf("Error: required POSIX PHP functions not available!\n"));
    }
    if (!function_exists("pcntl_exec")) {
        throw new \Exception(sprintf("Error: required PCNTL PHP functions not available!\n"));
    }

    $uid = posix_getuid();

    $httpuser = $context->getParamByName("apacheuser");
    if ($httpuser === false) {
        throw new \Exception(sprintf("%s\n", $context->errorMessage));
    }
    if ($httpuser == '') {
        $httpuser = $uid;
    }

    $envs = array();
    exec("env 2> /dev/null", $current_envvars, $ret);
    if ($ret === 0) {
        /*
         * Copy locale related environment variables from parent process
         * ("LANG", "LC_*", "LANGUAGE", and "NLSPATH").
        */
        foreach ($current_envvars as $var) {
            if (!preg_match('/^(?<name>LANG|LC_[^=]+|LANGUAGE|NLSPATH)=(?<value>.*)$/', $var, $reg)) {
                continue;
            }
            $envs[$reg['name']] = $reg['value'];
        }
    }
    $envs['wpub'] = $context->root;

    $envs['WIFF_ROOT'] = getenv("WIFF_ROOT");
    $envs['WIFF_CONTEXT_ROOT'] = $context->root;
    $envs['WIFF_CONTEXT_NAME'] = $context->name;
    $envs['pgservice_core'] = $context->getParamByName("core_db");
    $envs['PS1'] = sprintf("Anakeen-Control(%s)\\w\\$ ", $context->name);
    $envs['USER'] = $httpuser;
    if (getenv('PATH') !== false) {
        $envs['PATH'] = getenv('PATH');
    }
    if (getenv('TERM') !== false) {
        $envs['TERM'] = getenv('TERM');
    }

    if ($envs['pgservice_core'] === false || $envs['pgservice_core'] == '') {
        throw new \Exception(sprintf("Error: empty core_db parameter!\n"));
    }

    if (is_numeric($httpuser)) {
        $http_pw = posix_getpwuid($httpuser);
    } else {
        $http_pw = posix_getpwnam($httpuser);
    }
    if ($http_pw === false) {
        throw new \Exception(sprintf("Error: could not get information for httpuser '%s'\n", $httpuser));
    }

    $http_uid = $http_pw['uid'];
    $http_gid = $http_pw['gid'];

    $shell = array_shift($argv);
    if ($shell === null) {
        $shell = $http_pw['shell'];
    }

    $envs['HOME'] = $context->root;

    $ret = chdir($context->root);
    if ($ret === false) {
        throw new \Exception(sprintf("Error: could not chdir to '%s'\n", $context->root));
    }

    if ($uid != $http_uid) {
        $ret = posix_setgid($http_gid);
        if ($ret === false) {
            throw new \Exception(sprintf("Error: could not setgid to gid '%s'\n", $http_gid));

        }
        $ret = posix_setuid($http_uid);
        if ($ret === false) {
            throw new \Exception(sprintf("Error: could not setuid to uid '%s'\n", $http_uid));
        }
    }
    /** @noinspection PhpVoidFunctionResultUsedInspection Because it return false on error and void on success */


    $ret = pcntl_exec($shell, $argv, $envs);
    if ($ret === false) {
        throw new \Exception(sprintf("Error: exec error for '%s'\n", join(" ", array(
            $shell,
            join(" ", $argv)
        ))));
    }
    return 0;
}


function wiff_wstop(&$argv)
{
    $ctx_name = array_shift($argv);

    $wiff = WIFF::getInstance();

    $context = $wiff->getContext($ctx_name);
    if ($context === false) {
        printerr(sprintf("Error: could not get context '%s'!\n", $ctx_name));
        return 1;
    }

    $wstart = sprintf("%s/wstop", $context->root);
    if (!is_executable($wstart)) {
        printerr(sprintf("Error: wstop '%s' not found or not executable.\n", $wstart));
        return 1;
    }

    $cmd = sprintf("%s", escapeshellarg($wstart));
    system($cmd, $ret);

    return $ret;
}

function wiff_wstart(&$argv)
{
    $ctx_name = array_shift($argv);

    $wiff = WIFF::getInstance();

    $context = $wiff->getContext($ctx_name);
    if ($context === false) {
        printerr(sprintf("Error: could not get context '%s'!\n", $ctx_name));
        return 1;
    }

    $wstart = sprintf("%s/wstart", $context->root);
    if (!is_executable($wstart)) {
        printerr(sprintf("Error: wstart '%s' not found or not executable.\n", $wstart));
        return 1;
    }

    $cmd = sprintf("%s", escapeshellarg($wstart));
    system($cmd, $ret);

    return $ret;
}


function wiff_getParamValue($paramName)
{
    $wiffContextName = getenv('WIFF_CONTEXT_NAME');
    if ($wiffContextName === false || preg_match('/^\s*$/', $wiffContextName)) {
        printerr(sprintf("Error: WIFF_CONTEXT_NAME is not defined or empty.\n"));
        return false;
    }

    $wiff = WIFF::getInstance();
    $context = $wiff->getContext($wiffContextName);
    if ($context === false) {
        printerr(sprintf("Error: could not get context '%s': %s\n", $wiffContextName, $wiff->errorMessage));
        return false;
    }

    return $context->getParamByName($paramName);
}

function parse_argv_options(&$argv)
{
    $options = array();
    $m = array();
    while (count($argv) > 0 && preg_match('/^--/', $argv[0])) {
        if (preg_match('/^--([a-zA-Z0-9_-]+)=(.*)$/', $argv[0], $m)) {
            $options[$m[1]] = $m[2];
        } elseif (preg_match('/--([a-zA-Z0-9_-]+)$/', $argv[0], $m)) {
            $options[$m[1]] = true;
        } elseif (preg_match('/^--$/', $argv[0])) {
            array_shift($argv);
            return $options;
        }
        array_shift($argv);
    }

    return $options;
}

function boolopt($opt, &$options)
{
    if (array_key_exists($opt, $options) && $options[$opt]) {
        return true;
    }
    return false;
}


/**
 * change UID to the owner of the wiff script
 *
 * @param $path
 *
 * @return bool
 */
function setuid_wiff($path)
{
    $stat = stat($path);
    if ($stat === false) {
        printerr(sprintf("Error: could not stat '%s'!\n", $path));
        return false;
    }

    $uid = posix_getuid();

    $wiff_uid = $stat['uid'];
    $wiff_gid = $stat['gid'];

    if ($uid != $wiff_uid) {
        $ret = posix_setgid($wiff_gid);
        if ($ret === false) {
            printerr(sprintf("Error: could not setgid to gid '%s'.\n", $wiff_gid));
            return false;
        }
        $ret = posix_setuid($wiff_uid);
        if ($ret === false) {
            printerr(sprintf("Error: could not setuid to uid '%s'.\n", $wiff_uid));
            return false;
        }
    }

    return true;
}
