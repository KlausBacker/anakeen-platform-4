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
    public static function bashExec(array $cmds)
    {
        $bashlines[]="#!/bin/bash";
        $bashlines[]="set -eou pipefail";
        foreach ($cmds as $cmd) {
            JobLog::displayOutput("", "", "", $cmd);
            $bashlines[]=$cmd;
        }
        $bashFile=\Control\Internal\LibSystem::tempnam(null, "bashing");

        if (!file_put_contents($bashFile, implode("\n", $bashlines))) {
            throw new \Control\Exception\RuntimeException(sprintf("Cannot write bash file %s", escapeshellarg($bashFile)));
        }

        chmod($bashFile, 0700);
        exec("$bashFile 2>&1", $output, $retval);
        if ($retval !== 0) {
            $err = sprintf("%s\n[%s] %s", implode("\n", $bashlines), $retval, implode("\n", $output));
            if ($retval === SIGTERM) {
                JobLog::writeInterruption();
                throw new \Control\Exception\SignalException($err);
            } else {
                throw new \Control\Exception\RuntimeException($err);
            }
        }
    }
}
