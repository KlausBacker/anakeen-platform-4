<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Fullsearch\FilterContains;
use Anakeen\Search\SearchElements;

class PuSimpleSearch extends FulltextSearchConfig
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

    /**
     * Test Rank order Simple Get Document
     *
     * @dataProvider dataRankGetDocument
     * @param string $domain
     * @param string $searchPatten
     * @param array $expectedResults
     * @throws \Anakeen\Search\Exception
     */
    public function testRankContains($domain, $searchPatten, $expectedResults)
    {
        $s = new SearchElements();

        $filter = new FilterContains($domain, $searchPatten);
        $s->setSlice(10);
        $s->addFilter($filter);
        $s->setOrder($filter->getRankOrder());
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
            ["testDomainSimple", "lion", ["TST_ESIMPLE_002"]],
            ["testDomainSimple", "ours", ["TST_ESIMPLE_003", "TST_ESIMPLE_001"]],
            ["testDomainSimple", "saumon", ["TST_ESIMPLE_003", "TST_ESIMPLE_001"]],
            ["testDomainSimple", "salmonidés", ["TST_ESIMPLE_004", "TST_ESIMPLE_003"]],
        );
    }

    public function dataRankGetDocument()
    {
        return array(
            ["testDomainSimple", "lion", ["TST_ESIMPLE_002"]],
            ["testDomainSimple", "ours", ["TST_ESIMPLE_001", "TST_ESIMPLE_003"]],
            ["testDomainSimple", "saumon", ["TST_ESIMPLE_003", "TST_ESIMPLE_001"]]
        );
    }
}
