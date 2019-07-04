<?php


namespace Control\Internal;


use Symfony\Component\Console\Exception\RuntimeException;

class System
{
    public static function sudoWww()
    {

        require(__DIR__.'/../../../include/lib/Lib.Cli.php');

        $ret = setuid_wiff($_SERVER['SCRIPT_FILENAME']);
        if ($ret === false) {
            throw new RuntimeException("Cannot www sudo");
        }

    }

    public static function exec($cmd) {

        exec($cmd, $output, $retval);
        JobLog::displayOutput("", "", "", $cmd);
        if ($retval !== 0) {
            $err=sprintf("%s\n%s", $cmd, implode("\n", $output));
            throw new \Control\Exception\RuntimeException($err);
        }

}
}