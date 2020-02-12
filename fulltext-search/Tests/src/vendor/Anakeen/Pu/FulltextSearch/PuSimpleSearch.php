<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Core\DbManager;
use Anakeen\Fullsearch\FilterContains;
use Anakeen\Fullsearch\SearchDomainDatabase;
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
     *
     * @dataProvider dataGetDocument
     *
     */
    public function testContains($domain, $searchPatten, $expectedResults)
    {
        $s= new SearchElements();

        $filter=new FilterContains($domain, $searchPatten);
        $s->setSlice(10);
        $s->addFilter($filter);
        $results=$s->getResults();
        $names=[];
        foreach ($results as $smartElement) {
            $names[]=$smartElement->name;
        }

        print_r($s->getSearchInfo());
        DbManager::query("select * from searches.testdomainsimple", $results);
        $this->assertEquals($expectedResults, $names, print_r($s->getSearchInfo(), true));
    }

    public function dataGetDocument()
    {
        return array(
            ["testDomainSimple", "lion", ["TST_ESIMPLE_002"]],
            ["testDomainSimple","ours", ["TST_ESIMPLE_001"]]
        );
    }
}
