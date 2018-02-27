#!/usr/bin/env php
<?php

if (count($argv) !== 3 && count($argv) !== 4) {
    throw new Exception(sprintf("Invalid number of args: %d", count($argv)));
}

$configFile=__DIR__.'/../config/access.json';
if (! file_exists($configFile)) {
    file_put_contents($configFile, '{}');
}

$data=json_decode(file_get_contents($configFile), true);

$key=trim($argv[2]);

if ($argv[1] == "--get") {
    if (count($argv) !== 3) {
         throw new Exception(sprintf("Get Must have only 1 arg not %d", count($argv) - 2));
    }
    if (isset($data[$key])) {
        print $data[$key];
    }
}



if ($argv[1] == "--set") {

    if (count($argv) !== 4) {
        throw new Exception(sprintf("Get Must have only 2 args not %d", count($argv) - 2));
    }
    $data[$key]=$argv[3];
     file_put_contents($configFile, json_encode($data, JSON_PRETTY_PRINT));
}