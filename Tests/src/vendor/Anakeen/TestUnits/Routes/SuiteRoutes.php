<?php

namespace Anakeen\Pu\Routes;

use Dcp\Pu\FrameworkDcp;

require_once __DIR__ . "/../../WHAT/Lib.Prefix.php";
require_once 'WHAT/autoload.php';


class SuiteRoutes
{
    const LOGFILE = "/var/tmp/puapi.log";

    public static function suite()
    {
        self::configure();

        $r=new SuiteRoutes();
        return $r();
    }

    public function __invoke()
    {
        $suite = new FrameworkDcp();

        $suite->addTestSuite("Anakeen\\Pu\\Routes\\CoreDataDocument");
        $suite->addTestSuite("Anakeen\\Pu\\Routes\\CoreDataFamilyDocument");

        return $suite;
    }

    public static function configure()
    {
        @unlink(self::LOGFILE);
        ini_set("error_log", self::LOGFILE);
        printf("\nError log in %s\n", self::LOGFILE);
    }
}