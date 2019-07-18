<?php

namespace Anakeen\Script;

class Stdio implements IStdio
{
    /**
     * Wstart's stdout I/O interface
     * @param $msg
     */
    public function wstartStdout($msg)
    {
        file_put_contents("php://stdout", $msg);
    }
    /**
     * Wstart's stderr I/O interface
     * @param $msg
     */
    public function wstartStderr($msg)
    {
        if (mb_substr($msg, -1) != "\n") {
            $msg.= "\n";
        }
        file_put_contents("php://stderr", $msg);
    }
}
