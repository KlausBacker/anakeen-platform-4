<?php

/**
 * @var Composer\Autoload\ClassLoader $loader
 */
$loader = require __DIR__ . '/lib/vendor/autoload.php';

$loader->addPsr4('Anakeen\\', __DIR__ . '/');
//$loader->addPsr4('Dcp\\', __DIR__ . '/../Dcp/');

// Load all namespace from vendor directory
$loader->addPsr4('', dirname(__DIR__) . '/');


// Load generated SmartStrucure classes
$loader->addPsr4('SmartStructure\\', __DIR__ . '/../../'.\Anakeen\Core\Settings::DocumentGenDirectory.'/SmartStructure/');


// Add Legacy Autoloader
spl_autoload_register(function ($classname) {

    $classFile = sprintf("%s/../Root/Class.%s.php", __DIR__, $classname);
    if (file_exists($classFile)) {
       // error_log("Legacy require $classFile");
        require_once($classFile);
    } else {
        $classFile = sprintf("%s/../Root/%s.php", __DIR__, $classname);
        if (file_exists($classFile)) {
           // error_log("Legacy require $classFile");
            require_once($classFile);
        } else {
            error_log("Legacy not found $classFile");
        }
    }
});

\Anakeen\Core\Internal\Autoloader::recordLoader($loader);
