<?php

namespace Anakeen\Pu;

use Dcp\Pu\FrameworkDcp;

class SuiteUi
{
    public static function suite()
    {
        $r=new SuiteUi();
        return $r();
    }

    public function __invoke()
    {
        $suite = new FrameworkDcp();

        $suite->addTestSuite(\Anakeen\Pu\Mask\TestMask::class);
        $suite->addTestSuite(\Anakeen\Pu\TestSearchFamilies::class);
        $suite->addTestSuite(\Anakeen\Pu\TestImportCVDOC::class);

        return $suite;
    }
}
