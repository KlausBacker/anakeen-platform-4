<?php

namespace Dcp\Pu;

use Anakeen\Core\Utils\Postgres;

class TestPgArray extends TestCaseDcp
{
    /**
     * @dataProvider dataPgToArray
     */
    public function testPgToArray($pgValue, array $expectValues)
    {
        $values = Postgres::stringToArray($pgValue);
        $this->assertEquals($expectValues, $values, sprintf("wrong value : \n%s", print_r($values, true)));
        foreach ($expectValues as $k => $expectValue) {
            $this->assertTrue($expectValue === $values[$k], sprintf('not strict equal value "%s" <> "%s"', print_r($expectValue, true), print_r($values[$k], true)));
        }
    }

    /**
     * @dataProvider dataArrayToPg
     */
    public function testArrayToPg(array $values, $expect)
    {
        $values = Postgres::arrayToString($values);
        $this->assertEquals($expect, $values, "wrong value");
    }

    /**
     * @dataProvider dataErrorPgToArray
     */
    public function testErrorPgToArray($pgValue, $errorCode)
    {
        try {
            $values = Postgres::stringToArray($pgValue);
            $this->assertTrue(false, sprintf("Error must occurs: %s", print_r($values, true)));
        } catch (\Dcp\Db\Exception $e) {
            $this->assertEquals($errorCode, $e->getDcpCode(), "Not expected error");
        }
    }

    public static function dataErrorPgToArray()
    {
        return array(
            array(
                'zou',
                "DB0200"
            ),
            array(
                '{zo"uzou}',
                "DB0202"
            ),
            array(
                '{"zouzou" toto"}',
                "DB0201"
            ),
            array(
                '{,}',
                "DB0208"
            ),
            array(
                '{"zouzou toto, ty}',
                "DB0203"
            ),
            array(
                '"zouzou"',
                "DB0200"
            ),
            array(
                'zo{uzou}',
                "DB0200"
            ),
            array(
                'zouzou}',
                "DB0200"
            ),
            array(
                '{zouz}ou',
                "DB0200"
            ),
            array(
                '{{e},{"a}',
                "DB0203"
            ),
            array(
                '{{e},{a}',
                "DB0204"
            ),
            array(
                '{{e},{a}b}',
                "DB0205"
            ),
            array(
                '{{e},"a"}',
                "DB0206"
            ),
            array(
                '{{{e}},{{a}}}',
                "DB0207"
            ),
        );
    }

    public function dataArrayToPg()
    {

        return array(
            array(
                array(),
                "null"
            ),
            array(
                array(
                    1
                ),
                "{1}"
            ),

            array(
                array(
                    1,
                    2,
                    3
                ),
                "{1,2,3}"
            ),
            array(
                array(
                    "1",
                    "2",
                    "3"
                ),
                "{1,2,3}"
            ),
            array(
                array(
                    1,
                    '',
                    3
                ),
                "{1,NULL,3}"
            ),
            array(
                array(
                    " 1",
                    " 2",
                    " 3"
                ),
                '{" 1"," 2"," 3"}'
            ),
            array(
                array(
                    "1,2",
                    "1.3",
                    "3.14,159"
                ),
                '{"1,2",1.3,"3.14,159"}'
            ),
            array(
                array(
                    'enfin l"été"'
                ),
                '{"enfin l\\"été\\""}'
            ),
            array(
                array(
                    "l'été"
                ),
                "{l'été}"
            ),
            array(
                array(
                    "automne hiver"
                ),
                '{"automne hiver"}'
            ),
            array(
                array(
                    array()
                ),
                '{null}'
            ),
            array(
                array(
                    array(
                        1,
                        2,
                        3
                    )
                ),
                '{{1,2,3}}'
            ),
            array(
                array(
                    array(
                        1,
                        2,
                        3
                    ),
                    array(
                        4,
                        5,
                        6
                    ),
                    array(
                        7,
                        8,
                        9
                    )
                ),
                '{{1,2,3},{4,5,6},{7,8,9}}'
            ),
            array(
                array(
                    array(
                        1,
                        2,
                        3
                    ),
                    array(
                        4
                    ),
                    array(
                        5
                    )
                ),
                '{{1,2,3},{4,NULL,NULL},{5,NULL,NULL}}'
            ),
            array(
                array(
                    array(
                        1,
                        2,
                        3
                    ),
                    array(
                        4,
                        '',
                        null
                    ),
                    array(
                        null,
                        5
                    )
                ),
                '{{1,2,3},{4,NULL,NULL},{NULL,5,NULL}}'
            ),
            array(
                array(
                    array(),
                    array(
                        1
                    ),
                    array(
                        null,
                        2
                    ),
                    array(
                        null,
                        null,
                        3
                    )
                ),
                '{{NULL,NULL,NULL},{1,NULL,NULL},{NULL,2,NULL},{NULL,NULL,3}}'
            ),
            array(
                array(
                    array(
                        "007",
                        "James Bond"
                    ),
                    array(
                        "Secret,Agent"
                    ),
                    array(
                        "MI5"
                    )
                ),
                '{{007,"James Bond"},{"Secret,Agent",NULL},{MI5,NULL}}'
            )
        );
    }

    public function dataPgToArray()
    {
        return array(
            array(
                "{}",
                array()
            ),
            array(
                "{1}",
                array(
                    "1"
                )
            ),
            array(
                "{NULL}",
                array(
                    null
                )
            ),
            array(
                "{null}",
                array(
                    null
                )
            ),
            array(
                '{"null"}',
                array(
                    "null"
                )
            ),
            array(
                '{""}',
                array(
                    ""
                )
            ),
            array(
                '{"",""}',
                array(
                    "",
                    ""
                )
            ),
            array(
                "{alors}",
                array(
                    "alors"
                )
            ),
            array(
                "{alors ici}",
                array(
                    "alors ici"
                )
            ),
            array(
                '{"alors ici"}',
                array(
                    "alors ici"
                )
            ),
            array(
                '{1,02,30}',
                array(
                    "1",
                    "02",
                    "30"
                )
            ),
            array(
                '{1, 02, 30}',
                array(
                    "1",
                    "02",
                    "30"
                )
            ),
            array(
                '{ 1 , 02 , 30}',
                array(
                    "1",
                    "02",
                    "30"
                )
            ),
            array(
                '{ a b c , d  e  ,g   h}',
                array(
                    "a b c",
                    "d  e",
                    "g   h"
                )
            ),
            array(
                '{"1","02","30"}',
                array(
                    "1",
                    "02",
                    "30"
                )
            ),
            array(
                '{"1" ,  " 02 " ,  "30"}',
                array(
                    "1",
                    " 02 ",
                    "30"
                )
            ),
            array(
                '{"  1"," 02","-30"}',
                array(
                    "  1",
                    " 02",
                    "-30"
                )
            ),
            array(
                '{"ìíîï",òóôõöø,"àáâãäå"}',
                array(
                    "ìíîï",
                    "òóôõöø",
                    "àáâãäå"
                )
            ),
            array(
                '{"été",hivers,"automne"}',
                array(
                    "été",
                    "hivers",
                    "automne"
                )
            ),
            array(
                '{"été, ou printemps, ou autres",hivers,"automne"}',
                array(
                    "été, ou printemps, ou autres",
                    "hivers",
                    "automne"
                )
            ),
            array(
                '{"ét \"é\"","h\"ivers, automne","o\'connor"}',
                array(
                    'ét "é"',
                    'h"ivers, automne',
                    "o'connor"
                )
            ),
            array(
                '{{},{},{}}',
                array(
                    array(),
                    array(),
                    array(),
                )
            ),
            array(
                '{{},null,{}}',
                array(
                    array(),
                    null,
                    array(),
                )
            ),
            array(
                '{{},{""},{}}',
                array(
                    array(),
                    array(
                        ""
                    ),
                    array(),
                )
            ),
            array(
                '{null,{1},null}',
                array(
                    null,
                    array(
                        "1"
                    ),
                    null
                )
            ),
            array(
                '{{1},null,{2}}',
                array(
                    array(
                        "1"
                    ),
                    null,
                    array(
                        "2"
                    ),
                )
            ),
            array(
                '{1, null, 2}',
                array(
                    "1",
                    null,
                    "2",
                )
            ),

            array(
                '{{"été","hivers"},{"automne","printemps"}}',
                array(
                    array(
                        "été",
                        "hivers"
                    ),
                    array(
                        "automne",
                        "printemps"
                    )
                )
            ),
            array(
                '{{"été, hivers"},{"automne, printemps"}}',
                array(
                    array(
                        "été, hivers"
                    ),
                    array(
                        "automne, printemps"
                    )
                )
            ),
            array(
                '{{"{été}, hivers"},{"au{tomne, printemps"}}',
                array(
                    array(
                        "{été}, hivers"
                    ),
                    array(
                        "au{tomne, printemps"
                    )
                )
            ),
            array(
                '{{1,2,3},{4,5},{6}}',
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ),
                    array(
                        "4",
                        "5"
                    ),
                    array(
                        "6"
                    )
                )
            ),
            array(
                '{{1,2,3} ,{4,5} ,{6}}',
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ),
                    array(
                        "4",
                        "5"
                    ),
                    array(
                        "6"
                    )
                )
            ),
            array(
                '{{1,2,3}, {4,5}, {6}}',
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ),
                    array(
                        "4",
                        "5"
                    ),
                    array(
                        "6"
                    )
                )
            ),
            array(
                '{{1,2,3}, {4,5}, {6}}',
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ),
                    array(
                        "4",
                        "5"
                    ),
                    array(
                        "6"
                    )
                )
            ),
            array(
                "{{1, 2, 3}\n , {4, 5},\n{6}}",
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ),
                    array(
                        "4",
                        "5"
                    ),
                    array(
                        "6"
                    )
                )
            ),
            array(
                '{{1,2,3},{4,5,NULL},{6,NULL,NULL}}',
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ),
                    array(
                        "4",
                        "5",
                        null
                    ),
                    array(
                        "6",
                        null,
                        null
                    )
                )
            ),
            array(
                '{{1,2,3},{4,5,""},{6,"",""}}',
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ),
                    array(
                        "4",
                        "5",
                        ""
                    ),
                    array(
                        "6",
                        "",
                        ""
                    )
                )
            )
        );
    }
}
