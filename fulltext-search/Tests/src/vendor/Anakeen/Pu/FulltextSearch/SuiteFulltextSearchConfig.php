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

        $suite->addTestSuite(PuPatternQuery::class);
        $suite->addTestSuite(PuPatternSearch::class);
        $suite->addTestSuite(PuTextSearch::class);
        $suite->addTestSuite(PuDateSearch::class);
        $suite->addTestSuite(PuEnumSearch::class);
        $suite->addTestSuite(PuDocidSearch::class);
        $suite->addTestSuite(PuHtmltextSearch::class);
        $suite->addTestSuite(PuFileSearch::class);
        $suite->addTestSuite(PuCustomSearch::class);
        $suite->addTestSuite(PuCollectionSearch::class);

        return $suite;
    }
}
