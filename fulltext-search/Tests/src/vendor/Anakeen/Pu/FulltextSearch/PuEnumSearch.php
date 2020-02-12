<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Fullsearch\FilterContains;
use Anakeen\Search\SearchElements;

class PuEnumSearch extends FulltextSearchConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfiguration(__DIR__ . "/Config/tst_enum001.struct.xml");
        self::importDocument(__DIR__ . "/Config/tst_enum001.data.xml");
        self::importSearchConfiguration(__DIR__ . "/Config/enumFrSearchDomainConfig.xml");
        self::importSearchConfiguration(__DIR__ . "/Config/enumEnSearchDomainConfig.xml");
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
            ["testDomainEnumFr", "Rouge", ["TST_EENUM_001"]],
            ["testDomainEnumEn", "Red", ["TST_EENUM_001"]],
            ["testDomainEnumEn", "citrus", ["TST_EENUM_001","TST_EENUM_002"]],
            ["testDomainEnumEn", "blue", ["TST_EENUM_003","TST_EENUM_004"]],
            ["testDomainEnumEn", "cyan", ["TST_EENUM_001","TST_EENUM_003","TST_EENUM_004"]],
        );
    }
}
