<?php

namespace Anakeen\Pu\Config;

use Dcp\Pu\FrameworkDcp;

class SuiteConfig
{
    public static function suite()
    {
        $r=new SuiteConfig();
        return $r();
    }

    public function __invoke()
    {
        $suite = new FrameworkDcp();

        $suite->addTestSuite(\Anakeen\Pu\Config\PuStructBasic::class);
        $suite->addTestSuite(\Anakeen\Pu\Config\PuEnums::class);
        $suite->addTestSuite(\Anakeen\Pu\Config\PuTask::class);
        $suite->addTestSuite(\Anakeen\Pu\Config\PuDisableAccess::class);
        $suite->addTestSuite(\Anakeen\Pu\Lib\PuSeemMethod::class);

        return $suite;
    }
}
