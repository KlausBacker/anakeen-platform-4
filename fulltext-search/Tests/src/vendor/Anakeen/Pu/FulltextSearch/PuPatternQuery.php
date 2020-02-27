<?php

namespace Anakeen\Pu\FulltextSearch;

use Anakeen\Core\DbManager;
use Anakeen\Fullsearch\SearchDomainDatabase;

class PuPatternQuery extends FulltextSearchConfig
{

    /**
     * Test Simple Get Document
     * Order by default : title
     * @dataProvider dataGetDocument
     * @param string $stem
     * @param string $pattern
     * @param string $expectedQuery
     */
    public function testContains($stem, $pattern, $expectedQuery)
    {
        $tsQuery = SearchDomainDatabase::patternToTsquery($stem, $pattern);
        $sql = sprintf(
            "select to_tsquery('simple', E'%s')",
            pg_escape_string($tsQuery)
        );
        // Normalize query to test
        DbManager::query($sql, $tsNormQuery, true, true);
        $this->assertEquals($expectedQuery, $tsNormQuery);
    }


    public function dataGetDocument()
    {
        return array(
            ["french", "les caleçons", "'le' & 'calecon'"],
            ["french", "Février 2020", "'fevri' & '2020'"],
            ["english", "the bad -cats", "'bad' & !'cat'"],
            ["english", "the bad -cats -dogs", "'bad' & !'cat' & !'dog'"],
            ["english", 'the "bad cats"', "'bad' <-> 'cat'"],
            ["english", 'the "bad or cats"', "'bad' <2> 'cat'"],
            ["english", 'the dogs or cats', "'dog' | 'cat'"],
            ["english", 'the "bad cats" and "other dogs"', "'bad' <-> 'cat' & 'dog'"],
            ["english", 'the "bad cats" and "others dogs"', "'bad' <-> 'cat' & 'other' <-> 'dog'"],
            ["french", 'le sous-marin', "'sous-marin' & 'sous' & 'marin' & 'sous' & 'marin'"],
            ["english", 'the "bad cats" and -"others dogs"', "'bad' <-> 'cat' & !( 'other' <-> 'dog' )"],
            ["english", 'the "bad cats" and "others - dogs"', "'bad' <-> 'cat' & 'other' <-> 'dog'"],
            ["english", 'the "bad cats" or "others - dogs"', "'bad' <-> 'cat' | 'other' <-> 'dog'"],
            ["english", 'the "bad cats" or "other - dogs"', "'bad' <-> 'cat' | 'dog'"],
            ["english", '678 & chose', "'678' & 'chose'"],
            ["english", '""" )( dummy \\ query <->', "'dummi' & 'queri'"],
            ["english", 'signal -"segmentation fault"', "'signal' & !( 'segment' <-> 'fault' )"],
            ["french", "les cale*", "'le' & 'cale':*"],
            ["french", "les cale* long", "'le' & 'cale':* & 'long'"],
            ["simple", "les cale*ons", "'les' & 'cale' & 'ons'"],
            ["simple", "les 123*", "'les' & '123':*"],
            ["simple", "les mar* -truite -saumon", "'les' & 'mar':* & !'truite' & !'saumon'"],
            ["simple", "les truites or -saumon", "'les' & 'truites' | !'saumon'"],
            ["french", "les tortues d'Herman*", "'le' & 'tortu' & 'herman':*"],
            ["simple", "les tortues d'Herman*", "'les' & 'tortues' & 'd' & 'herman':*"],
        );
    }
}
