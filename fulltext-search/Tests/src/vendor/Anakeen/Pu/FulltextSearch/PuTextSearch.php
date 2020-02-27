<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Core\SEManager;
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

    /**
     * @dataProvider dataHighLight
     * @param $domain
     * @param $searchPatten
     * @param $expectedResults
     */
    public function testHighLight($domain, $searchPatten, $expectedResults)
    {
        $h = new \Anakeen\Fullsearch\FilterHighlight($domain);

        foreach ($expectedResults as $seName => $result) {
            $smartElement = SEManager::getDocument($seName);
            $light = $h->highlight($smartElement->id, $searchPatten);
            $this->assertContains($result, $light);
        }
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

    public function dataHighLight()
    {
        return [
            ["testDomainText", "lion", ["TST_ETEXT_002" => "La femelle du [lion] est la [lionne]"]],
            [
                "testDomainText",
                "ours",
                [
                    "TST_ETEXT_003" => "par les animaux sauvages ([ours] notamment) lors de leur remontée",
                    "TST_ETEXT_001" => "Tous les [ours] ont un grand corps trapu et massif",
                ]
            ],
            [
                "testDomainText",
                "saumon",
                [
                    "TST_ETEXT_003" => "[Saumon]: est un nom vernaculaire",
                    "TST_ETEXT_001" => "L'ours aime bien les [saumons] sauvages"
                ]
            ],
            [
                "testDomainText",
                "salmonidés",
                [
                    "TST_ETEXT_004" => "espèces de poissons de la famille des [salmonidés]",
                    "TST_ETEXT_003" => "espèces de poissons de la famille des [salmonidés]"
                ]
            ],
            [
                "testDomainText",
                "rivières de l'Atlas",
                [
                    "TST_ETEXT_004" => "les [rivières] de l'[Atlas] au Maroc"
                ]
            ],
        ];
    }
}
