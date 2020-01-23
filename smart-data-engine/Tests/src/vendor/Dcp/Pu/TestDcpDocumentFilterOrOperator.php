<?php

namespace Dcp\Pu;

use Anakeen\Search\Filters\OneDocumentTitle;

class TestDcpDocumentFilterOrOperator extends TestDcpDocumentFiltercommon
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_OPOPERATOR';

    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_OrOperator.csv"
        );
    }

    /**
     * @param $test
     * @dataProvider dataContainOrOperator
     */
    public function testContainOrOperator($test)
    {
        $filters = [];
        foreach ($test["values"] as $condition) {
            $filters[] = new \Anakeen\Search\Filters\OrOperator(
                new \Anakeen\Search\Filters\TitleContains($condition["value"], $condition["flags"] ?? null)
            );
        }

        $filter = new \Anakeen\Search\Filters\OrOperator(
            ...$filters
        );

        $this->common_testFilter(
            $test["fam"],
            $filter,
            $test["expected"]
        );
    }


    /**
     * @param $test
     * @dataProvider dataOneEqualsOrOperator
     */
    public function testOneEqualsOrOperator($test)
    {
        $filters = [];
        foreach ($test["values"] as $condition) {
            $filters[] =
                new \Anakeen\Search\Filters\OneEquals(
                    $condition["attrid"],
                    $condition["value"],
                    $condition["flags"] ?? null
                );
        }

        $filter = new \Anakeen\Search\Filters\OrOperator(
            ...$filters
        );

        $this->common_testFilter(
            $test["fam"],
            $filter,
            $test["expected"]
        );
    }


    /**
     * @param $test
     * @dataProvider dataOneDocumentTitleOrOperator
     */
    public function testOneDocumentTitleOrOperator($test)
    {
        $filters = [];
        foreach ($test["values"] as $condition) {
            $filters[] =
                new \Anakeen\Search\Filters\OneDocumentTitle(
                    $condition["attrid"],
                    $condition["value"],
                    $condition["flags"] ?? null
                );
        }

        $filter = new \Anakeen\Search\Filters\OrOperator(
            ...$filters
        );

        $this->common_testFilter(
            $test["fam"],
            $filter,
            $test["expected"]
        );
    }


    /**
     * @param $test
     * @dataProvider dataErrorOrOperator
     */
    public function testErrorOrOperator($test, $errorCode)
    {
        $filters = [];
        foreach ($test["values"] as $condition) {
            $filters[] =
                new \Anakeen\Search\Filters\OneDocumentTitle(
                    $condition["attrid"],
                    $condition["value"],
                    $condition["flags"] ?? null
                );
        }
        $filters[] =
            new \Anakeen\Search\Filters\HasUserTag(
                1234,
                "Hhoho"
            );

        $filter = new \Anakeen\Search\Filters\OrOperator(
            ...$filters
        );

        try {
            $this->common_testFilter(
                $test["fam"],
                $filter,
                []
            );
            $this->assertTrue(false, "Exception must occurs here");
        } catch (\Anakeen\Search\Filters\Exception $e) {
            $this->assertEquals($errorCode, $e->getDcpCode(), "No the good exception");
        }
    }

    public function dataContainOrOperator()
    {
        return array(
            array(
                array(
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "Hello"],
                        ["value" => "World"]
                    ],
                    "expected" => [
                        "TST_OPOR_1",
                        "TST_OPOR_2",
                        "TST_OPOR_3",
                        "TST_OPOR_4"
                    ]
                )
            ),

            array(
                array(
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "HeLLo", "flags" => \Anakeen\Search\Filters\TitleContains::NOCASE],
                        ["value" => "World"]
                    ],
                    "expected" => [
                        "TST_OPOR_1",
                        "TST_OPOR_2",
                        "TST_OPOR_3",
                        "TST_OPOR_4",
                        "TST_OPOR_6",
                        "TST_OPOR_7"
                    ]
                )
            ),

            array(
                array(
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "être"],
                        ["value" => "été"]
                    ],
                    "expected" => [
                        "TST_OPOR_10",
                        "TST_OPOR_9"
                    ]
                )
            ),


            array(
                array(
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "être", "flags" => \Anakeen\Search\Filters\TitleContains::NODIACRITIC],
                        ["value" => "été"]
                    ],
                    "expected" => [
                        "TST_OPOR_10",
                        "TST_OPOR_9",
                        "TST_OPOR_11"
                    ]
                )
            ),

            array(
                array(
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "être", "flags" => \Anakeen\Search\Filters\TitleContains::NODIACRITIC],
                        ["value" => "été", "flags" => \Anakeen\Search\Filters\TitleContains::NODIACRITIC]
                    ],
                    "expected" => [
                        "TST_OPOR_10",
                        "TST_OPOR_9",
                        "TST_OPOR_11",
                        "TST_OPOR_12"
                    ]
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "être", "flags" => \Anakeen\Search\Filters\TitleContains::NODIACRITIC],
                        ["value" => "été"],
                        ["value" => "World", "flags" => \Anakeen\Search\Filters\TitleContains::NOCASE]
                    ],
                    "expected" => [
                        "TST_OPOR_2",
                        "TST_OPOR_3",
                        "TST_OPOR_4",
                        "TST_OPOR_6",
                        "TST_OPOR_7",
                        "TST_OPOR_10",
                        "TST_OPOR_9",
                        "TST_OPOR_11",
                    ]
                )
            ),


        );
    }

    public function dataOneEqualsOrOperator()
    {
        return array(
            array(
                [
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "1", "attrid" => "tst_ints"],
                        ["value" => "2", "attrid" => "tst_ints"]
                    ],
                    "expected" => [
                        "TST_OPOR_1",
                        "TST_OPOR_3",
                        "TST_OPOR_4",
                        "TST_OPOR_5",
                        "TST_OPOR_6"
                    ]
                ]
            ),
            array(
                [
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "1", "attrid" => "tst_ints"],
                        ["value" => "6", "attrid" => "tst_ints"],
                        ["value" => "2", "attrid" => "tst_ints"]
                    ],
                    "expected" => [
                        "TST_OPOR_1",
                        "TST_OPOR_2",
                        "TST_OPOR_3",
                        "TST_OPOR_4",
                        "TST_OPOR_5",
                        "TST_OPOR_6"
                    ]
                ]
            )
        );
    }

    public function dataOneDocumentTitleOrOperator()
    {
        return array(
            array(
                [
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "Hello", "attrid" => "tst_docids"],
                        ["value" => "World", "attrid" => "tst_docids"]
                    ],
                    "expected" => [
                        "TST_OPOR_2",
                        "TST_OPOR_3",
                        "TST_OPOR_4",
                        "TST_OPOR_5",
                        "TST_OPOR_6"
                    ]
                ]
            ),
            array(
                [
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "été", "attrid" => "tst_docids"],
                        ["value" => "être", "attrid" => "tst_docids"]
                    ],
                    "expected" => [
                        "TST_OPOR_10",
                        "TST_OPOR_11",
                        "TST_OPOR_12",
                    ]
                ]
            ),

            array(
                [
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "été", "attrid" => "tst_docids"],
                        ["value" => "jupiter", "attrid" => "tst_docids", "flags" => OneDocumentTitle::NOCASE]
                    ],
                    "expected" => [
                        "TST_OPOR_10",
                        "TST_OPOR_11",
                        "TST_OPOR_13",
                    ]
                ]
            )
        );
    }

    public function dataErrorOrOperator()
    {
        return array(
            array(
                [
                    "fam" => self::FAM,
                    "values" => [
                        ["value" => "Hello", "attrid" => "tst_docids"],
                        ["value" => "World", "attrid" => "tst_docids"]
                    ]
                ]
                ,"FLT0010"
            )
        );
    }
}
