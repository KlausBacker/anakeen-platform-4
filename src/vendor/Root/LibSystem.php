<?php
/**
 * LibSystem class
 *
 * This class provides methods for querying system informations
 *
 */

class LibSystem
{
    public static function getCommandPath($cmdname)
    {
        $path_env = getenv("PATH");
        if ($path_env == false) {
            return false;
        }
        foreach (explode(":", $path_env) as $path) {
            if (file_exists("$path/$cmdname")) {
                return "$path/$cmdname";
            }
        }
        return false;
    }
    
    public static function getHostName()
    {
        return php_uname('n');
    }
    
    public static function getHostIPAddress($hostname = "")
    {
        if ($hostname == false) {
            $hostname = LibSystem::getHostName();
        }
        $ip = gethostbyname($hostname);
        if ($ip == $hostname) {
            return false;
        }
        return $ip;
    }
    
    public static function getServerName()
    {
        return getenv("SERVER_NAME");
    }
    
    public static function getServerAddr()
    {
        return getenv("SERVER_ADDR");
    }
    
    public static function runningInHttpd()
    {
        return LibSystem::getServerAddr();
    }
    
    public static function ssystem($args, $opt = null)
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
    
    public static function getAbsolutePath($path)
    {
        if (is_link($path)) {
            $path = readlink($path);
        }
        return realpath($path);
    }
    
    public static function tempnam($dir, $prefix)
    {
        if ($dir === null || $dir === false) {
            $dir = \Anakeen\Core\ContextManager::getTmpDir();
        }
        return tempnam($dir, $prefix);
    }
    /**
     * force new index
     */
    public static function reloadLocaleCache()
    {
        exec(sprintf("%s/whattext 2>&1", escapeshellarg(DEFAULT_PUBDIR)), $output, $ret);
        if ($ret) {
            error_log(implode(",", $output));
        }
    }
}
