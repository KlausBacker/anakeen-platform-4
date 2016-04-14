<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * WiffLibSystem class
 *
 * This class provides methods for querying system informations
 *
 * @author Anakeen
 */

class WiffLibSystem
{
    
    static function getCommandPath($cmdname)
    {
        $path_env = getenv("PATH");
        if ($path_env == false) {
            return false;
        }
        foreach (preg_split("/:/", $path_env) as $path) {
            if (file_exists("$path/$cmdname")) {
                return "$path/$cmdname";
            }
        }
        /* If the command has not been found it may be
         * because of open_basedir restriction.
         * In this case, try to detect with the which
         * command.
        */
        if (ini_get("open_basedir") != '') {
            $out = array();
            $ret = 0;
            $cmd = sprintf("which %s", escapeshellarg($cmdname));
            exec($cmd, $out, $ret);
            if ($ret == 0) {
                return $out[0];
            }
        }
        
        return false;
    }
    
    static function getHostName()
    {
        return php_uname('n');
    }
    
    static function getHostIPAddress($hostname = "")
    {
        if ($hostname == false) {
            $hostname = WiffLibSystem::getHostName();
        }
        $ip = gethostbyname($hostname);
        if ($ip == $hostname) {
            return false;
        }
        return $ip;
    }
    
    static function getServerName()
    {
        return getenv("SERVER_NAME");
    }
    
    static function getServerAddr()
    {
        return getenv("SERVER_ADDR");
    }
    
    static function runningInHttpd()
    {
        return WiffLibSystem::getServerAddr();
    }
    /**
     * system() à la Perl's system(@cmd)
     * @param $args
     * @param array|null $opt
     * @return int
     */
    static function ssystem($args, $opt = null)
    {
        $pid = pcntl_fork();
        if ($pid == - 1) {
            return -1;
        }
        if ($pid != 0) {
            $ret = pcntl_waitpid($pid, $status);
            if ($ret == - 1) {
                return -1;
            }
            return pcntl_wexitstatus($status);
        }
        $envs = array();
        if ($opt && array_key_exists('envs', $opt) && is_array($opt['envs'])) {
            $envs = $opt['envs'];
        }
        if ($opt && array_key_exists('closestdin', $opt) && $opt['closestdin'] === true) {
            fclose(STDIN);
        }
        if ($opt && array_key_exists('closestdout', $opt) && $opt['closestdout'] === true) {
            fclose(STDOUT);
        }
        if ($opt && array_key_exists('closestderr', $opt) && $opt['closestderr'] === true) {
            fclose(STDERR);
        }
        $cmd = array_shift($args);
        pcntl_exec($cmd, $args, $envs);
        return 0;
    }
    
    static function getAbsolutePath($path)
    {
        if (is_link($path)) {
            $path = readlink($path);
        }
        return realpath($path);
    }
    
    static function tempnam($dir, $prefix)
    {
        if ($dir === null || $dir === false) {
            $dir = null;
            foreach (array(
                'TMP',
                'TMPDIR'
            ) as $env) {
                $dir = getenv($env);
                if ($dir !== false && is_dir($dir) && is_writable($dir)) {
                    break;
                }
            }
        }
        if ($dir === null || $dir === false) {
            $dir = null;
            foreach (array(
                '/tmp',
                '/var/tmp'
            ) as $tmpdir) {
                if (is_dir($tmpdir) && is_writable($tmpdir)) {
                    $dir = $tmpdir;
                    break;
                }
            }
        }
        $res = tempnam($dir, $prefix);
        return $res;
    }
}
