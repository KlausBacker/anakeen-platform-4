<?php

namespace Anakeen\TransformationEngine;

class Client
{
    const status_waiting = "?";
    const error_connect = "?";
    const status_inprogress = "?";
    const error_convert = "?";
    const status_done = "?";
}

class Manager
{
    const Ns = "TE";

    public static function vaultGenerate($engine, $vidin, $vidout, $isimage = false, $docid = '')
    {
        $err = "";
        return $err;
    }
}