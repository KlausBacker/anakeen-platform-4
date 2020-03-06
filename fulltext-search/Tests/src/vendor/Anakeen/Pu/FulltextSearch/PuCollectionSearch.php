<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Fullsearch\FilterMatch;
use Anakeen\Search\SearchElements;

class PuCollectionSearch extends FulltextSearchConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfiguration(__DIR__ . "/Config/tst_simple003.struct.xml");
        self::importDocument(__DIR__ . "/Config/tst_simple003.data.xml");
        self::importSearchConfiguration(__DIR__ . "/Config/collectionSearchDomainConfig.xml");
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
        $s->setOrder("name, id");
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
            ["testDomainCollection", "ours", ["TST_ESIMPLE3_001"]],
            ["testDomainCollection", "Le lion", ["TST_ESIMPLE3_002"]],
            ["testDomainCollection", "Saumon", ["TST_ESIMPLE3_003"]],
            ["testDomainCollection", "Truite", []],
            ["testDomainCollection", "Ours or Lion or Saumon or Truite", ["TST_ESIMPLE3_001","TST_ESIMPLE3_002","TST_ESIMPLE3_003"]],
        );
    }

}
