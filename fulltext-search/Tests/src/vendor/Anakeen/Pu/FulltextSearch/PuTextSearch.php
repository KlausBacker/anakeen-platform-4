<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Fullsearch\FilterContains;
use Anakeen\Search\SearchElements;

class PuTextSearch extends FulltextSearchConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfiguration(__DIR__ . "/Config/tst_text001.struct.xml");
        self::importDocument(__DIR__ . "/Config/tst_text001.data.xml");
        self::importSearchConfiguration(__DIR__ . "/Config/textSearchDomainConfig.xml");
    }

    /**
     * Test Text Get Document
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
     * Test Rank order Text Get Document
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
            ["testDomainText", "lion", ["TST_ETEXT_002"]],
            ["testDomainText", "ours", ["TST_ETEXT_003", "TST_ETEXT_001"]],
            ["testDomainText", "saumon", ["TST_ETEXT_003", "TST_ETEXT_001"]],
            ["testDomainText", "salmonidés", ["TST_ETEXT_004", "TST_ETEXT_003"]],
        );
    }

    public function dataRankGetDocument()
    {
        return array(
            ["testDomainText", "lion", ["TST_ETEXT_002"]],
            ["testDomainText", "ours", ["TST_ETEXT_001", "TST_ETEXT_003"]],
            ["testDomainText", "saumon", ["TST_ETEXT_003", "TST_ETEXT_001"]]
        );
    }
}
