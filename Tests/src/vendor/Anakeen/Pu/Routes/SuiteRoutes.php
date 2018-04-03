<?php

namespace Anakeen\Pu\Routes;

use Dcp\Pu\FrameworkDcp;

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

        $suite->addTestSuite(\Anakeen\Pu\Routes\CoreDocument\PuCoreDataDocument::class);
        $suite->addTestSuite(\Anakeen\Pu\Routes\CoreFamily\PuCoreDataFamilyDocument::class);
        $suite->addTestSuite(\Anakeen\Pu\Routes\RouteAccess\PuCoreRouteAccess::class);

        return $suite;
    }

    public static function configure()
    {
        @unlink(self::LOGFILE);
        ini_set("error_log", self::LOGFILE);
        printf("\nError log in %s\n", self::LOGFILE);
    }
}
