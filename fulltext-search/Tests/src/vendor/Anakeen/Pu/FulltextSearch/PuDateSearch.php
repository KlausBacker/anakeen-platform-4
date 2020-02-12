<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Core\DbManager;
use Anakeen\Fullsearch\FilterContains;
use Anakeen\Search\SearchElements;

class PuDateSearch extends FulltextSearchConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfiguration(__DIR__ . "/Config/tst_date001.struct.xml");
        self::importDocument(__DIR__ . "/Config/tst_date001.data.xml");
        self::importSearchConfiguration(__DIR__ . "/Config/dateFrSearchDomainConfig.xml");
        self::importSearchConfiguration(__DIR__ . "/Config/dateEnSearchDomainConfig.xml");
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
            ["testDomainDateFr", "Vendredi", ["TST_EDATE_001"]],
            ["testDomainDateFr", "Décembre", ["TST_EDATE_001", "TST_EDATE_003"]],
            ["testDomainDateFr", "1903", ["TST_EDATE_001"]],
            ["testDomainDateFr", "2020", ["TST_EDATE_001", "TST_EDATE_002", "TST_EDATE_003"]],
            ["testDomainDateFr", "2020 février", ["TST_EDATE_001", "TST_EDATE_002"]],
            ["testDomainDateFr", "samedi fevrier", ["TST_EDATE_001", "TST_EDATE_002"]],
            ["testDomainDateEn", "Friday", ["TST_EDATE_001"]],
            ["testDomainDateEn", "December", ["TST_EDATE_001", "TST_EDATE_003"]],
            ["testDomainDateEn", "2020 february", ["TST_EDATE_001", "TST_EDATE_002"]],
            ["testDomainDateEn", "saturday february", ["TST_EDATE_001", "TST_EDATE_002"]],
        );
    }
}
