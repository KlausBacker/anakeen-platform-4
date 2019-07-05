<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Wcontrol library
 * @author Anakeen
 */


require_once 'class/Class.WIFF.php';
/**
 * evaluate a Process object
 * @param Process $process
 * @return array
 */
function wcontrol_eval_process(Process $process)
{
    $msg = "";
    switch ($process->getName()) {
        case "check":
            if (function_exists("wcontrol_check_" . $process->getAttribute('type'))) {
                $ret = false;
                eval("\$ret = wcontrol_check_" . $process->getAttribute('type') . "(\$process);");
                
                if (function_exists("wcontrol_msg_" . $process->getAttribute('type'))) {
                    eval("\$msg = wcontrol_msg_" . $process->getAttribute('type') . "(\$process);");
                } else {
                    $msg = generic_msg($process);
                }

                return array(
                    'ret' => $ret,
                    'output' => $msg
                );
            }
            break;
        case "process":
            return wcontrol_process($process);
        case "download":
            return wcontrol_download($process);
        case "unpack":
            return wcontrol_unpack($process);
        case "clean-unpack":
            return wcontrol_clean_unpack($process);
        case "purge-unreferenced-parameters-value":
            return wcontrol_purge_unreferenced_parameters_value($process);
        case "unregister-module":
            return wcontrol_unregister_module($process);
        case "check-files":
            return wcontrol_check_files($process);
    }
    
    return array(
        'ret' => false,
        'output' => sprintf("Unknown process with name '%s'", $process->getName())
    );
}

function wcontrol_check_files(Process $process)
{
    $module = $process->phase->module;

    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_files' (module = '%s')", $module->name));

    $ret = $module->checkManifestFiles();
    
    return array(
        'ret' => $ret ? true : false,
        'output' => $module->errorMessage ? $module->errorMessage : "Ok"
    );
}

function wcontrol_unregister_module(Process $process)
{
    $wiff = WIFF::getInstance();

    $moduleName = $process->phase->module->name;
    $context = $process->phase->module->getContext();

    $wiff->activity(sprintf("* Execute 'unregister_module' (context = '%s', module = '%s')", $context->name, $moduleName));

    $ret = $context->removeModule($moduleName);
    if ($ret === false) {
        return array(
            "ret" => $ret,
            "output" => $context->errorMessage
        );
    }
    
    $ret = $context->deleteFilesFromModule($moduleName);
    if ($ret === false) {
        return array(
            "ret" => $ret,
            "output" => $context->errorMessage
        );
    }
    
    $ret = $context->deleteManifestForModule($moduleName);
    return array(
        "ret" => $ret ? true : false,
        "output" => $context->errorMessage ? $context->errorMessage : "Ok"
    );
}

function wcontrol_purge_unreferenced_parameters_value(Process $process)
{
    $context = $process->phase->module->getContext();

    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'purge_unreferenced_parameters_value' (context = '%s')", $context->name));

    $ret = $context->purgeUnreferencedParametersValue();
    
    return array(
        "ret" => $ret ? true : false,
        "output" => $ret ? "Ok" : sprintf("Error purging unreferenced parameters value in context '%s': %s", $context->name, $context->errorMessage)
    );
}

function wcontrol_unpack(Process $process)
{
    $module = $process->phase->module;
    $context = $module->getContext();

    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'unpack' (context = '%s', module = '%s')", $context->name, $module->name));

    $ret = $module->unpack($context->root);
    
    return array(
        'ret' => $ret ? true : false,
        'output' => $module->errorMessage ? $module->errorMessage : "Ok"
    );
}

function wcontrol_clean_unpack(Process $process)
{
    $module = $process->phase->module;
    $context = $module->getContext();

    $wiff = WIFF::getInstance();
    $wiff->activity("* Execute 'clean_unpack' (context = '%s', module = '%s')", $context->name, $module->name);

    $ret = $context->deleteFilesFromModule($module->name);
    if ($ret === false) {
        return array(
            "ret" => $ret,
            "output" => $context->errorMessage
        );
    }
    
    return wcontrol_unpack($process);
}
/**
 * Execute Process
 * @return array
 * @param Process $process
 */
function wcontrol_process(Process $process)
{

    
    $cmd = $process->getAttribute('command');
    if ($cmd == '') {
        return array(
            'ret' => false,
            'output' => "Missing, or empty, 'command' attribute in process."
        );
    }
    if (!preg_match('|^\s*/|', $cmd)) {
        $ctx_root = getenv('WIFF_CONTEXT_ROOT');
        if ($ctx_root === false) {
            return array(
                'ret' => false,
                'output' => 'WIFF_CONTEXT_ROOT env variable is not defined.'
            );
        }
        $cmd = sprintf("%s/%s", escapeshellarg($ctx_root) , $cmd);
    }
    
    $cmd = $process->phase->module->getContext()->expandParamsValues($cmd);

    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'process' (command = '%s')", $cmd));

    $current_version = false;
    $installedModule = $process->phase->module->getContext()->getModuleInstalled($process->phase->module->name);
    if ($installedModule !== false) {
        $current_version = $installedModule->version;
    }
    if ($current_version !== false) {
        putenv(sprintf('MODULE_VERSION_FROM=%s', $current_version));
    }
    putenv(sprintf('MODULE_VERSION_TO=%s', $process->phase->module->version));
    
    $tmpfile = Control\Internal\LibSystem::tempnam(null, 'wcontrol_process');
    if ($tmpfile === false) {
        return array(
            'ret' => false,
            'output' => 'Error creating temporary file.'
        );
    }
    $tmpscript = Control\Internal\LibSystem::tempnam(null, 'wcontrol_process');
    if ($tmpscript === false) {
        return array(
            'ret' => false,
            'output' => 'Error creating temporary script.'
        );
    }
    if (chmod($tmpscript, 0700) === false) {
        return array(
            'ret' => false,
            'output' => sprintf("Error setting execution mode on temporary script.", $tmpscript)
        );
    }
    /* Use `/bin/sh` as system() would do */
    $cmd = "#!/bin/sh\n" . $cmd;
    if (file_put_contents($tmpscript, $cmd) === false) {
        return array(
            'ret' => false,
            'output' => sprintf("Error writing content to temporary script '%s'.", $tmpscript)
        );
    }
    system(sprintf("%s 1> %s 2>&1 < /dev/null", escapeshellarg($tmpscript) , escapeshellarg($tmpfile)) , $ret);
    
    $output = file_get_contents($tmpfile);
    unlink($tmpfile);
    unlink($tmpscript);
    
    return array(
        'ret' => ($ret === 0) ? true : false,
        'output' => $output
    );
}

function wcontrol_download(Process & $process)
{
    require_once ('class/Class.WIFF.php');
    require_once ('class/Class.Process.php');
    
    $href = $process->getAttribute('href');
    $href = $process->phase->module->getContext()->expandParamsValues($href);

    $action = $process->getAttribute('action');

    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'download' (href = '%s' -> '%s', action = '%s')", $process->getAttribute('href'), $href, $action));

    $localFile = $wiff->downloadUrl($href);
    if ($localFile === false) {
        return array(
            'ret' => false,
            'output' => sprintf("Error downloading '%s'", $href)
        );
    }
    
    $actionProcess = new Process(sprintf("<process command=\"%s\" />", $action) , $process->phase);
    $actionProcess->attributes['command'] = sprintf("%s %s", $action, $localFile);
    $status = wcontrol_process($actionProcess);
    unlink($localFile);
    $status_ret = $status['ret'];
    $status_output = $status['output'];
    
    if ($status_ret === false) {
        return array(
            'ret' => false,
            'output' => sprintf("Error executing action for href '%s': %s", $href, $status_output)
        );
    }
    
    return array(
        'ret' => true,
        'output' => "Ok"
    );
}
/**
 * generic message
 * @param Process $process
 * @return string
 */
function generic_msg($process)
{
    return sprintf("Checking process with type '%s'", $process->getAttribute('type'));
}
/**
 * phpfunction check
 * @param Process $process
 * @return bool
 */

function wcontrol_check_phpfunction($process)
{
    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_phpfunction' (function = '%s')", $process->getAttribute('function')));

    return function_exists($process->getAttribute('function'));
}

function wcontrol_msg_phpfunction(Process $process)
{
    return sprintf("Checking if the PHP function '%s' exists", $process->getAttribute('function'));
}
/**
 * exec check
 * @param Process $process
 * @return bool
 */

function wcontrol_check_exec($process)
{

    $cmd = $process->getAttribute('cmd');
    $cmd = $process->phase->module->getContext()->expandParamsValues($cmd);

    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_exec' (cmd = '%s' -> '%s')", $process->getAttribute('cmd'), $cmd));

    exec($cmd, $output, $ret);
    return ($ret === 0) ? true : false;
}

function wcontrol_msg_exec(Process $process)
{
    return sprintf("Checking if the command '%s' returns a success exit code", $process->getAttribute('cmd'));
}
/**
 * file check
 * @param Process $process
 * @return bool
 */

function wcontrol_check_file($process)
{
    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_file' (file = '%s', predicate = '%s')", $process->getAttribute('file'), $process->getAttribute('predicate')));

    switch ($process->getAttribute('predicate')) {
        case 'file_exists':
        case 'e':
        case '-e':
        case 'a':
        case '-a':
            return file_exists($process->getAttribute('file'));
            break;

        case 'is_dir':
        case 'd':
        case '-d':
            return is_dir($process->getAttribute('file'));
            break;

        case 'is_file':
        case 'f':
        case '-f':
            return is_file($process->getAttribute('file'));
            break;

        case 'is_link':
        case 'L':
        case '-L':
            return is_link($process->getAttribute('file'));
            break;

        case 'is_readable':
        case 'r':
        case '-r':
            return is_readable($process->getAttribute('file'));
            break;

        case 'is_writable':
        case 'w':
        case '-w':
            return is_writable($process->getAttribute('file'));
            break;

        case 'is_executable':
        case 'x':
        case '-x':
            return is_executable($process->getAttribute('file'));
            break;

        default:
            return false;
    }
}

function wcontrol_msg_file(Process $process)
{
    return sprintf("Checking if the file '%s' validate the predicate '%s'", $process->getAttribute('file') , $process->getAttribute('predicate'));
}
/**
 * syscommand check
 * @param Process $process
 * @return bool
 */

function wcontrol_check_syscommand(Process $process)
{
    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_syscommand' (command = '%s')", $process->getAttribute('command')));

    $ret = Control\Internal\LibSystem::getCommandPath($process->getAttribute('command'));
    if ($ret === false) {
        return false;
    }
    return true;
}

function wcontrol_msg_syscommand(Process $process)
{
    return sprintf("Checking if the command '%s' is in the PATH", $process->getAttribute('command'));
}
/**
 * pearmodule check
 * @param Process $process
 * @return bool
 */

function wcontrol_check_pearmodule(Process $process)
{
    return wcontrol_check_phpclass($process);
}

function wcontrol_check_phpclass(Process $process)
{
    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_phpclass' (include = '%s', class = '%s')", $process->getAttribute('include'), $process->getAttribute('class')));

    $include = $process->getAttribute('include');
    if ($include != "") {
        $ret = @include_once ($include);
        if ($ret == false) {
            return false;
        }
    }
    if (!class_exists($process->getAttribute('class') , false)) {
        return false;
    }
    return true;
}

function wcontrol_msg_pearmodule(Process $process)
{
    return wcontrol_msg_phpclass($process);
}

function wcontrol_msg_phpclass(Process $process)
{
    return sprintf("Checking if the class '%s' is available in include file '%s'", $process->getAttribute('class') , $process->getAttribute('include'));
}
/**
 * apachemodule check
 * @param Process $process
 * @return bool
 */

function wcontrol_check_apachemodule(Process $process)
{
    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_apachemodule' (module = '%s')", $process->getAttribute('module')));

    if (!function_exists('apache_get_modules')) {
        return true;
    }
    $mods = apache_get_modules();
    if (in_array($process->getAttribute('module') , $mods)) {
        return true;
    }
    return false;
}

function wcontrol_msg_apachemodule(Process $process)
{
    return sprintf("Checking if the Apache module '%s' is loaded", $process->getAttribute('module'));
}
/**
 * pgversion check
 * @param Process $process
 * @return bool
 */

function wcontrol_check_pgversion(Process & $process)
{
    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_pgversion' (service = '%s', predicated='%s', version='%s')", $process->getAttribute('service'), $process->getAttribute('predicate'), $process->getAttribute('version')));

    if (!function_exists('pg_connect')) {
        $process->errorMessage = 'PHP function pg_connect() not available. You might need to install a php-pg package from your distribution in order to have Postgresql support in PHP.</help>';
        return false;
    }
    
    require_once ('class/Class.WIFF.php');
    
    $service = $process->getAttribute('service');
    $predicate = $process->getAttribute('predicate');
    $version = $process->getAttribute('version');
    
    $wiff = WIFF::getInstance();
    $service = $wiff->expandParamValue($service);
    
    if ($service == "") {
        return false;
    }
    if ($version == "") {
        return false;
    }
    
    $conn = @pg_connect("service='$service'");
    if ($conn === false) {
        $process->errorMessage = "Connection failed. Maybe postgresql server is not started or database '" . $service . "' does not exist. ";
        return false;
    }
    
    $res = pg_query($conn, "SHOW SERVER_VERSION");
    if ($res === false) {
        pg_close($conn);
        return false;
    }
    $row = pg_fetch_row($res);
    if ($row === false) {
        pg_close($conn);
        return false;
    }
    
    $cmp = version_compare($row[0], $version);
    
    $return = true;
    $op = "";
    switch ($predicate) {
        case 'eq':
            $op = "equal to";
            $return = ($cmp == 0);
            break;

        case 'ne':
            $op = "not equal to";
            $return = ($cmp != 0);
            break;

        case 'lt':
            $op = "less than";
            $return = ($cmp < 0);
            break;

        case 'le':
            $op = "less than or equal to";
            $return = ($cmp <= 0);
            break;

        case 'gt':
            $op = "greater than";
            $return = ($cmp > 0);
            break;

        case 'ge':
            $op = "greater or equal to";
            $return = ($cmp >= 0);
            break;
    }
    
    if (!$return) {
        $process->errorMessage = "Server version (currently " . $row[0] . ") must be " . $op . " " . $version . ".";
        return false;
    }
    
    $encoding = pg_client_encoding($conn);
    
    if (!in_array(strtolower($encoding) , array(
        'unicode',
        'utf8'
    ))) {
        $process->errorMessage = "Database encoding : " . $encoding . ". UTF8 required. ";
        return false;
    }
    
    return true;
}

function wcontrol_msg_pgversion(Process $process)
{
    if ($process->errorMessage) {
        return $process->errorMessage;
    } else {
        return "";
    }
}
/**
 * phpversion check
 * @param Process $process
 * @return bool
 */

function wcontrol_check_phpversion(Process & $process)
{
    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_pgversion' (predicate = '%s', version='%s')", $process->getAttribute('predicate'), $process->getAttribute('version')));

    $predicate = $process->getAttribute('predicate');
    $version = $process->getAttribute('version');
    
    $return = true;
    $op = "";
    switch ($predicate) {
        case 'eq':
            $op = "equal to";
            $return = (version_compare(PHP_VERSION, $version) === 0) ? true : false;
            break;

        case 'ne':
            $op = "not equal to";
            $return = (version_compare(PHP_VERSION, $version) !== 0) ? true : false;
            break;

        case 'lt':
            $op = "less than";
            $return = (version_compare(PHP_VERSION, $version) < 0) ? true : false;
            break;

        case 'le':
            $op = "less than or equal to";
            $return = (version_compare(PHP_VERSION, $version) <= 0) ? true : false;
            break;

        case 'gt':
            $op = "greater than";
            $return = (version_compare(PHP_VERSION, $version) > 0) ? true : false;
            break;

        case 'ge':
            $op = "greater or equal to";
            $return = (version_compare(PHP_VERSION, $version) >= 0) ? true : false;
            break;
    }
    
    if (!$return) {
        $process->errorMessage = "PHP version (currently " . PHP_VERSION . ") must be " . $op . " " . $version . ".";
        return false;
    }
    
    return true;
}

function wcontrol_msg_phpversion(Process $process)
{
    if ($process->errorMessage) {
        return $process->errorMessage;
    } else {
        return "";
    }
}
/**
 * pgempty check
 * @param Process $process
 * @return bool
 */

function wcontrol_check_pgempty(Process & $process)
{
    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_pgempty' (service = '%s')", $process->getAttribute('service')));

    if (!function_exists('pg_connect')) {
        $process->errorMessage = 'PHP function pg_connect() not available. You might need to install a php-pg package from your distribution in order to have Postgresql support in PHP.</help>';
        return false;
    }
    
    require_once ('class/Class.WIFF.php');
    
    $service = $process->getAttribute('service');
    
    $wiff = WIFF::getInstance();
    $service = $wiff->expandParamValue($service);
    
    if ($service == "") {
        return false;
    }
    
    $conn = pg_connect("service='$service'");
    if ($conn === false) {
        $process->errorMessage = "Connection failed. Maybe postgresql server is not started or database '" . $service . "' does not exist. ";
        return false;
    }
    // Test if database is empty
    $res = pg_query($conn, "SELECT COUNT(*) FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('information_schema','pg_catalog');");
    if ($res === false) {
        pg_close($conn);
        return false;
    }
    $row = pg_fetch_row($res);
    if ($row === false) {
        pg_close($conn);
        return false;
    }
    
    $table_number = $row[0];
    if ($table_number != 0) {
        $process->errorMessage = "Database for service " . $service . " is not empty of user defined tables (" . $table_number . " found).";
        return false;
    }
    
    return true;
}

function wcontrol_msg_pgempty(Process $process)
{
    if ($process->errorMessage) {
        return $process->errorMessage;
    } else {
        return "";
    }
}

function wcontrol_check_ncurses(Process & $process)
{
    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_ncurses'"));

    ob_start();
    
    system('php -r "print function_exists("ncurses_init");"');
    
    $result = ob_get_contents();
    
    ob_end_clean();
    
    if ($result != 1) {
        return false;
    }
    
    return true;
}

function wcontrol_msg_ncurses(Process $process)
{
    return "";
}
/**
 * PHP bug #45996
 * @param Process $process
 * @return bool
 */
function wcontrol_check_phpbug45996(Process & $process)
{
    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("Execute 'check_phpbug45996'"));

    $expected = "a'b";
    $vals = array();
    $index = array();
    
    $xmldata = <<<EOXML
<?xml version="1.0"?>
<p>a&apos;b</p>
EOXML;
    
    $xml_parser = xml_parser_create();
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
    xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 0);
    xml_parse_into_struct($xml_parser, $xmldata, $vals, $index);
    
    if ($vals[0]['value'] != $expected) {
        return false;
    }
    return true;
}

function wcontrol_msg_phpbug45996(Process & $process)
{
    return sprintf("Checking for PHP bug #45996");
}
/**
 * PHP bug #40926
 * @param Process $process
 * @return bool
 */
function wcontrol_check_phpbug40926(Process & $process)
{
    $wiff = WIFF::getInstance();
    $wiff->activity(sprintf("* Execute 'check_phpbug40926'"));


    require_once ('class/Class.WIFF.php');
    
    $wiff = WIFF::getInstance();
    $service = $process->getAttribute('service');
    $service = $wiff->expandParamValue($service);
    
    $php = Control\Internal\LibSystem::getCommandPath('php');
    if ($php === false) {
        $wiff->log(LOG_ERR, __FUNCTION__ . " " . sprintf("PHP CLI not found."));
        return false;
    }
    
    $tmpfile = Control\Internal\LibSystem::tempnam(null, 'WIFF_phpbug40926');
    if ($tmpfile === false) {
        $wiff->log(LOG_ERR, __FUNCTION__ . " " . sprintf("Error creating temporary file."));
        return false;
    }
    
    $testcode = <<<EOF
<?php
pg_connect('service=$service');
exit(0);
?>
EOF;
    
    $ret = file_put_contents($tmpfile, $testcode);
    if ($ret === false) {
        $wiff->log(LOG_ERR, __FUNCTION__ . " " . sprintf("Error writing to temporary file '%s'.", $tmpfile));
        unlink($tmpfile);
        return false;
    }
    
    $out = array();
    $ret = 0;
    $cmd = sprintf('%s %s', escapeshellarg($php) , escapeshellarg($tmpfile));
    exec($cmd, $out, $ret);
    if ($ret != 0) {
        unlink($tmpfile);
        return false;
    }
    
    unlink($tmpfile);
    return true;
}

function wcontrol_msg_phpbug40926(Process & $process)
{
    return sprintf("Checking for PHP bug #40926");
}
