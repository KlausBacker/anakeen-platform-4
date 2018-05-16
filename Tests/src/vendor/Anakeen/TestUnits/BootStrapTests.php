<?php


require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../WHAT/Lib.Prefix.php';

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);


\Anakeen\TestUnits\CoreTests::configure();
printf("\nError log in [%s].\n", \Anakeen\TestUnits\CoreTests::LOGFILE);