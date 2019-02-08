<?php

namespace Dcp\Pu;

class TestSearchJoin extends TestCaseDcpCommonFamily
{
    /**
     * import TST_SEARCHJOIN1 and TST_SEARCHJOIN2 family
     *
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_search_join.ods";
    }

    /**
     * Test join search
     *
     * @param string $join      join query
     * @param string $filter    filters query
     * @param string $filterVar Variable for filter
     *
     * @dataProvider dataSearchJoin
     */
    public function testSearchJoinExecute($join, $filter, $filterVar)
    {
        $s = new \Anakeen\Search\Internal\SearchSmartData(self::$dbaccess, "TST_SEARCHJOIN2");
        $s->join($join);
        $s->addFilter($filter, $filterVar);
        $s->search();
        $array = $s->getSearchInfo();
        $err = $array["error"];
        $this->assertEmpty($err, "An error was found when trying a join search:" . $err);
    }

    public function dataSearchJoin()
    {
        return array(
            array(
                "tst_title = tst_searchjoin1(tst_title)",
                "tst_searchjoin1.tst_attr1 = '%s'",
                "Youpi"
            ),
            array(
                "tst_join::int = tst_searchjoin1(initid)",
                "lower(tst_searchjoin1.tst_title) = '%s'",
                "youpi"
            ),
            array(
                "tst_join::int = tst_searchjoin1(initid)",
                "tst_searchjoin1.tst_title = '%s'",
                "youpi"
            ),
            array(
                "tst_title = tst_searchjoin1(tst_title)",
                "tst_searchjoin1.tst_attr1 = '%s' OR tst_searchjoin1.tst_attr1 = 'Youpla'",
                "Youpi"
            ),
            array(
                "tst_title = tst_searchjoin1(tst_title)",
                "lower(tst_searchjoin1.tst_attr1) = lower('%s') OR lower(upper(tst_searchjoin1.tst_attr1)) = lower(upper('Youpla'))",
                "Youpi"
            )
        );
    }
}
