<?php

namespace Anakeen\Script;

class Stdio implements IStdio
{
    /**
     * Wstart's stdout I/O interface
     * @param $msg
     */
    public function wstart_stdout($msg)
    {
        fputs(STDOUT, $msg);
    }
    /**
     * Wstart's stderr I/O interface
     * @param $msg
     */
    public function wstart_stderr($msg)
    {
        if (mb_substr($msg, -1) != "\n") {
            $msg.= "\n";
        }
        fputs(STDERR, $msg);
    }
}
