<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Fullsearch\FilterContains;
use Anakeen\Search\SearchElements;

class PuDocidSearch extends FulltextSearchConfig
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfiguration(__DIR__ . "/Config/tst_docid002.struct.xml");
        self::importConfiguration(__DIR__ . "/Config/tst_docid001.struct.xml");
        self::importDocument(__DIR__ . "/Config/tst_docid002.data.xml");
        self::importDocument(__DIR__ . "/Config/tst_docid001.data.xml");
        self::importSearchConfiguration(__DIR__ . "/Config/docidSearchDomainConfig.xml");
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
            ["testDomainDocid", "Rouge", ["TST_EDOCID1_001","TST_EDOCID1_004"]],
            ["testDomainDocid", "Skywalker", ["TST_EDOCID1_001","TST_EDOCID1_005","TST_EDOCID1_007"]],
            ["testDomainDocid", "Solo", ["TST_EDOCID1_001","TST_EDOCID1_007"]],
            ["testDomainDocid", "Solo et Skywalker", ["TST_EDOCID1_001","TST_EDOCID1_007"]],
            ["testDomainDocid", "Solo, panthère et Skywalker", ["TST_EDOCID1_001"]],
            ["testDomainDocid", "Obiwan Kenobi", ["TST_EDOCID1_006"]],
        );
    }
}
