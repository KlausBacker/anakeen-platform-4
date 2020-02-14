<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Fullsearch\FilterContains;
use Anakeen\Search\SearchElements;

class PuPatternSearch extends FulltextSearchConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfiguration(__DIR__ . "/Config/tst_simple001.struct.xml");
        self::importDocument(__DIR__ . "/Config/tst_simple001.data.xml");
        self::importSearchConfiguration(__DIR__ . "/Config/simpleSearchDomainConfig.xml");
    }

    /**
     * Test Simple Get Document
     * Order by default : title
     * @dataProvider dataGetDocument
     * @param string $domain
     * @param string $searchPatten
     * @param array $expectedResults
     * @throws \Anakeen\Search\Exception
     */
    public function testContains($domain, $searchPatten, $expectedResults)
    {
        $s = new SearchElements();

        $filter = new FilterContains($domain, $searchPatten);
        $s->setSlice(10);
        $s->addFilter($filter);
        $results = $s->getResults();
        $names = [];
        foreach ($results as $smartElement) {
            $names[] = $smartElement->name;
        }

        $this->assertEquals($expectedResults, $names, print_r($s->getSearchInfo(), true));
    }


    public function dataGetDocument()
    {
        return array(
            ["testDomainSimple", "lion or ours", ["TST_ESIMPLE_001", "TST_ESIMPLE_002", "TST_ESIMPLE_003"]],
            ["testDomainSimple", "ours or saumon or truite or lion", ["TST_ESIMPLE_001", "TST_ESIMPLE_002", "TST_ESIMPLE_003", "TST_ESIMPLE_004"]],
            ["testDomainSimple", "ours -saumon", []],
            ["testDomainSimple", "ours -océan", ["TST_ESIMPLE_001"]],
            ["testDomainSimple", '"espèces de poissons"', ["TST_ESIMPLE_003", "TST_ESIMPLE_004"]],
            ["testDomainSimple", '"rivière maroc"', []],
            ["testDomainSimple", '"rivière de l\'Atlas au Maroc"', ["TST_ESIMPLE_004"]],
            ["testDomainSimple", 'rivière maroc', ["TST_ESIMPLE_004"]],
            ["testDomainSimple", '15 or 1993', ["TST_ESIMPLE_002", "TST_ESIMPLE_004"]],
            ["testDomainSimple", 'saumon -"espèces de poissons"', ["TST_ESIMPLE_001"]],
            ["testDomainSimple", 'mar*', ["TST_ESIMPLE_001","TST_ESIMPLE_003", "TST_ESIMPLE_004"]],
            ["testDomainSimple", 'mar* -truite -saumon', []],
        );
    }

}
