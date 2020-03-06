<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Fullsearch\FilterMatch;
use Anakeen\Search\SearchElements;

class PuCustomSearch extends FulltextSearchConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfiguration(__DIR__ . "/Config/tst_simple002.struct.xml");
        self::importDocument(__DIR__ . "/Config/tst_simple002.data.xml");
        self::importSearchConfiguration(__DIR__ . "/Config/customSearchDomainConfig.xml");
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

        $filter = new FilterMatch($domain, $searchPatten);
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
            ["testDomainCustom", "CREATE", ["TST_ESIMPLE2_001", "TST_ESIMPLE2_002", "TST_ESIMPLE2_003", "TST_ESIMPLE2_004"]],
            ["testDomainCustom", "TST_ESIMPLE2_001", ["TST_ESIMPLE2_001"]],
            ["testDomainCustom", "TST_ESIMPLE2_002", ["TST_ESIMPLE2_002"]],
            ["testDomainCustom", "TST_ESIMPLE2_003", ["TST_ESIMPLE2_003"]],
            ["testDomainCustom", "TST_ESIMPLE2_004", ["TST_ESIMPLE2_004"]],
        );
    }

}
