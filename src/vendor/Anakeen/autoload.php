<?php

$loader = require __DIR__ . '/lib/vendor/autoload.php';
//@TODO Find Another way to add UI autoload
require __DIR__ . '/Ui/PhpLib/vendor/autoload.php';

$loader->addPsr4('Anakeen\\', __DIR__ . '/');
$loader->addPsr4('Dcp\\', __DIR__ . '/../Dcp/');

spl_autoload_register(function ($classname) {

    $classFile=sprintf("%s/../Root/Class.%s.php", __DIR__, $classname);
    if (file_exists($classFile)) {
        error_log("Legacy require $classFile");
        require_once($classFile);
    } else {
        $classFile = sprintf("%s/../Root/%s.php", __DIR__, $classname);
        if (file_exists($classFile)) {
            error_log("Legacy require $classFile");
            require_once($classFile);
        } else {
             error_log("Legacy not found $classFile");
        }
    }


});