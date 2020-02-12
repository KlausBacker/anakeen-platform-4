<?php

namespace Anakeen\Pu\FulltextSearch;

use Dcp\Pu\FrameworkDcp;

class SuiteFulltextSearchConfig
{
    public static function suite()
    {
        $r=new SuiteFulltextSearchConfig();
        return $r();
    }

    public function __invoke()
    {
        $suite = new FrameworkDcp();

        $suite->addTestSuite(PuSimpleSearch::class);

        return $suite;
    }
}
