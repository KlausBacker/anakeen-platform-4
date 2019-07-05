<?php


namespace Control\Internal;


use Symfony\Component\Console\Exception\RuntimeException;

class System
{
    public static function sudoWww()
    {

        require(__DIR__ . '/../../../include/lib/Lib.Cli.php');

        $ret = setuid_wiff($_SERVER['SCRIPT_FILENAME']);
        if ($ret === false) {
            throw new RuntimeException("Cannot www sudo");
        }

    }

    public static function exec($cmd)
    {

        JobLog::displayOutput("", "", "", $cmd);
        exec($cmd, $output, $retval);
        if ($retval !== 0) {
            $err = sprintf("%s\n[%s] %s", $cmd, $retval, implode("\n", $output));
            if ($retval === SIGTERM) {
                JobLog::writeInterruption();
                throw new \Control\Exception\SignalException($err);
            } else {
                throw new \Control\Exception\RuntimeException($err);
            }
        }

    }
}