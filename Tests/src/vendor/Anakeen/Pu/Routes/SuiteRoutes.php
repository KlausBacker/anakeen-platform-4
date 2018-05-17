<?php

namespace Anakeen\Pu\Routes;

use Dcp\Pu\FrameworkDcp;

class SuiteRoutes
{
    public static function suite()
    {
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
}
