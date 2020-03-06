<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Core\DbManager;
use Anakeen\Fullsearch\FilterMatch;
use Anakeen\Search\SearchElements;

class PuHtmltextSearch extends FulltextSearchConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfiguration(__DIR__ . "/Config/tst_htmltext001.struct.xml");
        self::importDocument(__DIR__ . "/Config/tst_htmltext001.data.xml");
        self::importSearchConfiguration(__DIR__ . "/Config/htmltestSearchDomainConfig.xml");
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
            ["testDomainHtml", "Ours", ["TST_EHTML_001"]],
            ["testDomainHtml", "grimpeurs habiles", ["TST_EHTML_001"]],
            ["testDomainHtml", "cheval", ["TST_EHTML_002"]],
            ["testDomainHtml", "chevaux", ["TST_EHTML_002"]],
            ["testDomainHtml", "sommaire", ["TST_EHTML_002"]],
            ["testDomainHtml", "headline", []],
            ["testDomainHtml", "title", []],
            ["testDomainHtml", "h3", []],

        );
    }
}
