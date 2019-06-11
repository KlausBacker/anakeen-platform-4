<?php

namespace Anakeen\Script;

interface IStdio
{
    public function wstartStdout($msg);

    public function wstartStderr($msg);
}
