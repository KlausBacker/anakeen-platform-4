<?php

namespace Control\Internal;

/**
 * Control\Internal\LibSystem class
 *
 * This class provides methods for querying system informations
 *
 */
class LibSystem
{
    protected static $tmpFiles = [];

    static public function getCommandPath($cmdname)
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

    static public function getHostName()
    {
        return php_uname('n');
    }

    static public function getHostIPAddress($hostname = "")
    {
        if ($hostname == false) {
            $hostname = \Control\Internal\LibSystem::getHostName();
        }
        $ip = gethostbyname($hostname);
        if ($ip == $hostname) {
            return false;
        }
        return $ip;
    }

    static public function getServerName()
    {
        return getenv("SERVER_NAME");
    }

    static public function getServerAddr()
    {
        return getenv("SERVER_ADDR");
    }

    static public function runningInHttpd()
    {
        return self::getServerAddr();
    }

    /**
     * system() à la Perl's system(@cmd)
     *
     * @param            $args
     * @param array|null $opt
     *
     * @return int
     */
    static public function ssystem($args, $opt = null)
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            return -1;
        }
        if ($pid != 0) {
            $ret = pcntl_waitpid($pid, $status);
            if ($ret == -1) {
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

    static public function getAbsolutePath($path)
    {
        if (is_link($path)) {
            $path = readlink($path);
        }
        return realpath($path);
    }

    static public function tempnam($dir, $prefix)
    {
        if ($dir === null || $dir === false) {
            $dir = null;
            foreach (
                array(
                    'TMP',
                    'TMPDIR'
                ) as $env
            ) {
                $dir = getenv($env);
                if ($dir !== false && is_dir($dir) && is_writable($dir)) {
                    break;
                }
            }
        }
        if ($dir === null || $dir === false) {
            $dir = null;
            foreach (
                array(
                    '/tmp',
                    '/var/tmp'
                ) as $tmpdir
            ) {
                if (is_dir($tmpdir) && is_writable($tmpdir)) {
                    $dir = $tmpdir;
                    break;
                }
            }
        }
        $dir.="/anakeen-control";
        if (! is_dir($dir)) {
            mkdir($dir);
        }
        $res = tempnam($dir, $prefix);
        self::$tmpFiles[] = $res;

        return $res;
    }

    static public function purgeTmpFiles()
    {
        foreach (self::$tmpFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
