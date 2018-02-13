<?php

namespace Anakeen\Pu\Routes;

use Dcp\HttpApi\V1\Crud\Exception;
use Dcp\HttpApi\V1\DocManager\DocManager;
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

        $suite->addTestSuite("Anakeen\\Pu\\Routes\\CoreGetDocument");
        $suite->addTestSuite("Anakeen\\Pu\\Routes\\CorePutDocument");
        $suite->addTestSuite("Anakeen\\Pu\\Routes\\CoreGetFamilyDocument");

        return $suite;
    }

    public static function configure()
    {
        @unlink(self::LOGFILE);
        ini_set("error_log", self::LOGFILE);
        printf("\nError log in %s\n", self::LOGFILE);
    }
}
