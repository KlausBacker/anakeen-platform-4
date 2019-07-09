<?php

/**
 * Check for required PHP classes/functions on server
 * @param array $errors
 */
function checkDependencies(& $errors = array())
{


    // Check PHP version
    if (version_compare(PHP_VERSION, '7.1') < 0) {
        array_push($errors, sprintf("PHP version %s is not supported: you must use PHP >= 7.1.", PHP_VERSION));
    }
    // Check for required classes
    foreach (array(
                 'DOMDocument' => 'dom',
                 'Collator' => 'intl'
             ) as $class => $extension) {
        if (!class_exists($class, false)) {
            array_push($errors,
                sprintf("PHP class '%s' not found: you might need to install the PHP '%s' extension.", $class,
                    $extension));
        }
    }
    // Check for required functions
    foreach (array(
                 'json_encode' => 'json',
                 'json_decode' => 'json',
                 'xml_parse' => 'xml',
                 'date' => 'date',
                 'preg_match' => 'pcre',
                 'pg_connect' => 'pgsql',
                 'curl_init' => 'curl'
             ) as $function => $extension) {
        if (!function_exists($function)) {
            array_push($errors,
                sprintf("PHP function '%s' not found: you might need to install the PHP '%s' extension.", $function,
                    $extension));
        }
    }
    // Check for required system commands
    foreach (array(
                 'zip',
                 'unzip',
                 'pg_dump',
                 'tar',
                 'gzip'
             ) as $cmd) {
        $cmdPath = Control\Internal\LibSystem::getCommandPath($cmd);
        if ($cmdPath === false) {
            array_push($errors, sprintf("System command '%s' not found in PATH.", $cmd));
        }
    }
}

/**
 * Check for required dependencies and initialize config
 * @param array $errors
 * @return bool
 */
function checkInitServer(&$errors = array())
{

    $confDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'conf';
    $templateDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'template';

    error_log($confDir);

    if (!is_dir($confDir)) {
        mkdir($confDir);
    }

    $lockFile = $confDir . DIRECTORY_SEPARATOR . 'checkInitServer.lock';
    if (($lock = fopen($lockFile, 'c')) === false) {
        array_push($errors, sprintf("Error creating lock file '%s'", $lockFile));
        return false;
    }
    if (flock($lock, LOCK_EX) === false) {
        array_push($errors, sprintf("Error obtaining exclusive access on lock file '%s'", $lockFile));
        fclose($lock);
        return false;
    }
    $errors = array();

    checkDependencies($errors);

    $conf = [
        $confDir . DIRECTORY_SEPARATOR . 'params.xml' => $templateDir . DIRECTORY_SEPARATOR . 'params.xml',
        $confDir . DIRECTORY_SEPARATOR . 'contexts.xml' => $templateDir . DIRECTORY_SEPARATOR . 'contexts.xml'
    ];

    // Initialize xml conf files
    foreach (array_keys($conf) as $file) {
        if (is_file($file)) {
            continue;
        }
        if (!is_file($conf[$file])) {
            array_push($errors, sprintf("Could not find '%s' file.", $conf[$file]));
            continue;
        }
        $fout = fopen($file, 'x');
        if ($fout === false) {
            array_push($errors, sprintf("Could not create '%s' file.", $file));
            continue;
        }
        $content = @file_get_contents($conf[$file]);
        if ($content === false) {
            array_push($errors, sprintf("Error reading content of '%s'.", $conf[$file]));
            fclose($fout);
            continue;
        }
        $ret = fwrite($fout, $content);
        if ($ret === false) {
            array_push($errors, sprintf("Error writing content to '%s'.", $file));
            fclose($fout);
            continue;
        }
        fclose($fout);
    }

    flock($lock, LOCK_UN);
    fclose($lock);
    return (count($errors) <= 0);
}
