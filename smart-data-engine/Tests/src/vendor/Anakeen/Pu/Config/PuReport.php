<?php

namespace Anakeen\Pu\Config;

use Anakeen\Core\SEManager;
use phpDocumentor\Reflection\Types\Boolean;
use SmartStructure\Fields\Devbill;
use SmartStructure\Fields\Iuser;
use SmartStructure\Fields\Report;

/**
 * To get debug trace, add the --debug option to phpunit
 * Class PuReport
 * @package Anakeen\Pu\Config
 */
class PuReport extends TestCaseConfig
{
    const CONTAINS = "~*";
    const NOT_CONTAINS = "!~*";
    const ONE_EQUALS = "~y";
    const STARTS_WITH = "~^";
    const EQUAL = "=";
    const NOT_EQUAL = "!=";
    const IS_NULL = "is null";
    const IS_NOT_NULL = "is not null";
    const GREATER_THAN = ">";
    const GREATER_THAN_EQ = ">=";
    const LOWER_THAN = "<";
    const LOWER_THAN_EQ = "<=";
    const TITLE_CONTAINS = "=~*";
    const RELATIONS_NAME = ["FOO_1", "FOO_2", "FOO_3", "FOO_4", "ACCOUNT 1", "ACCOUNT 2", "ACCOUNT 3", "ACCOUNT_4"];
    private static $idMap;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_report_all_types.struct.xml");

        $foo1 = SEManager::createDocument("DEVBILL");
        $foo1->setAttributeValue(Devbill::bill_title, "FOO_1");
        $foo1->store();

        $foo2 = SEManager::createDocument("DEVBILL");
        $foo2->setAttributeValue(Devbill::bill_title, "FOO_2");
        $foo2->store();

        $foo3 = SEManager::createDocument("DEVBILL");
        $foo3->setAttributeValue(Devbill::bill_title, "FOO_3");
        $foo3->store();

        $foo4 = SEManager::createDocument("DEVBILL");
        $foo4->setAttributeValue(Devbill::bill_title, "FOO_4");
        $foo4->store();

        $user1 = SEManager::createDocument("IUSER");
        $user1->setAttributeValue(Iuser::us_fname, "ACCOUNT");
        $user1->setAttributeValue(Iuser::us_lname, "1");
        $user1->setAttributeValue(Iuser::us_login, "toto");
        $user1->store();

        $user2 = SEManager::createDocument("IUSER");
        $user2->setAttributeValue(Iuser::us_fname, "ACCOUNT");
        $user2->setAttributeValue(Iuser::us_lname, "2");
        $user2->setAttributeValue(Iuser::us_login, "titi");
        $user2->store();

        $user3 = SEManager::createDocument("IUSER");
        $user3->setAttributeValue(Iuser::us_fname, "ACCOUNT");
        $user3->setAttributeValue(Iuser::us_lname, "3");
        $user3->setAttributeValue(Iuser::us_login, "tutu");
        $user3->store();

        $user4 = SEManager::createDocument("IUSER");
        $user4->setAttributeValue(Iuser::us_fname, "ACCOUNT");
        $user4->setAttributeValue(Iuser::us_lname, "4");
        $user4->setAttributeValue(Iuser::us_login, "tete");
        $user4->store();

        self::$idMap = [
            "FOO_1" => $foo1->initid,
            "FOO_2" => $foo2->initid,
            "FOO_3" => $foo3->initid,
            "FOO_4" => $foo4->initid,
            "ACCOUNT 1" => $user1->initid,
            "ACCOUNT 2" => $user2->initid,
            "ACCOUNT 3" => $user3->initid,
            "ACCOUNT 4" => $user4->initid,
        ];
    }

    /**
     * @param array $smartElementToFind
     * @param array $reportData
     * @param array $expectedSEFound
     * @param $seProcess
     * @param $reportProcess
     * @param string $skippedTestMessage
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Core\SmartStructure\SmartFieldAccessException
     * @throws \Anakeen\Database\Exception
     * @throws \Anakeen\Exception
     */
    public function genericTest(
        array $smartElementToFind,
        array $reportData,
        array $expectedSEFound,
        $seProcess,
        $reportProcess,
        string $skippedTestMessage = ""
    ) {
        $debug = in_array('--debug', $_SERVER['argv'], true);

        $debug ? print "\n\n" : null;
        $allTypeStructure = SEManager::getFamily("TST_REPORT_ALLTYPE");
        $this->assertNotEmpty($allTypeStructure, "Structure TST_REPORT_ALLTYPE not found");


        foreach ($smartElementToFind as $name => $attrs) {
            $elt = SEManager::createDocument($allTypeStructure->id);
            $elt->setAttributeValue("test_ddui_all__title", $name);
            foreach ($attrs as $id => $value) {
                $seProcess($elt, $id, $value);
            }
            $elt->store();
        }

        $reportStructure = SEManager::getFamily("REPORT");
        $this->assertNotEmpty($reportStructure, "Structure REPORT not found");
        $report = SEManager::createDocument($reportStructure->id);
        $report->setAttributeValue(Report::se_famid, $allTypeStructure->id);
        foreach ($reportData as $id => $value) {
            $reportProcess($report, $id, $value);
            if ($debug) {
                if ($id === Report::se_attrids) {
                    print "\n ATTRIBUTE :";
                    print_r($value);
                } elseif ($id === Report::se_funcs) {
                    print "\nOPERATOR :";
                    print_r($value);
                }
            }
        }
        $report->store();

        if ($debug) {
            $query = $report->getAttributeValue(Report::se_sqlselect);
            print "\nQUERY : $query";
        }

        $results = [];
        foreach ($report->getContent() as $foundSE) {
            $results[] = $foundSE["title"];
        }


        if (!empty($skippedTestMessage)) {
            self::markTestSkipped($skippedTestMessage);
        } else {
            self::assertEquals($expectedSEFound, $results, "\nReport found elements are not correct");
        }

        if ($debug) {
            print "\n";
        }
    }

    /**
     * @dataProvider dataFieldDefault
     * @dataProvider dataFieldEmptyValue
     * @param array $smartElementToFind
     * @param array $reportData
     * @param array $expectedSEFound
     * @param string $skippedTestMessage
     */
    public function testFieldDefault(
        array $smartElementToFind,
        array $reportData,
        array $expectedSEFound,
        string $skippedTestMessage = ""
    ) {
        $seProcess = function ($elt, $id, $value) {
            $elt->setAttributeValue($id, $value);
        };

        $reportProcess = function ($report, $id, $value) {
            $report->setAttributeValue($id, $value);
        };

        $this->genericTest(
            $smartElementToFind,
            $reportData,
            $expectedSEFound,
            $seProcess,
            $reportProcess,
            $skippedTestMessage
        );
    }

    public function dataFieldDefault()
    {
        return [
            /****************************************** TYPES SIMPLES *****************************************************/

            /**************************** text ******************************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => ["u"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::EQUAL],
                    Report::se_keys => ["Un"],

                ],
                [
                    "TST_1"
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::STARTS_WITH],
                    Report::se_keys => ["d"],

                ],
                [
                    "TST_2"
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::NOT_EQUAL],
                    Report::se_keys => ["Un"],

                ],
                [
                    "TST_0",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ["n"],

                ],
                [
                    "TST_0",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /************** longtext ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext" => "Un\nDeux"
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext" => "Deux\nTrois"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => ["u"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext" => "Un\nDeux"
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext" => "Deux\nTrois"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext"],
                    Report::se_funcs => [self::STARTS_WITH],
                    Report::se_keys => ["de"],

                ],
                [
                    "TST_2"
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext" => "Un\nDeux"
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext" => "Deux\nTrois"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ["o"],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext" => "Un\nDeux"
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext" => "Deux\nTrois"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext" => "Un\nDeux"
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext" => "Deux\nTrois"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** htmltext ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext" => "<p><b>Un</b><em>Deux</em></p>"
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext" => "<p><b>Deux</b><em>Trois</em></p>"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => ["u"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext" => "<p><b>Un</b><em>Deux</em></p>"
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext" => "<p><b>Deux</b><em>Trois</em></p>"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ["o"],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext" => "<p><b>Un</b><em>Deux</em></p>"
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext" => "<p><b>Deux</b><em>Trois</em></p>"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext" => "<p><b>Un</b><em>Deux</em></p>"
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext" => "<p><b>Deux</b><em>Trois</em></p>"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** int ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::EQUAL],
                    Report::se_keys => [1],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::NOT_EQUAL],
                    Report::se_keys => [2],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::GREATER_THAN],
                    Report::se_keys => [1],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::GREATER_THAN_EQ],
                    Report::se_keys => [1],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::LOWER_THAN],
                    Report::se_keys => [1],
                ],
                []
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::LOWER_THAN_EQ],
                    Report::se_keys => [1],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** double ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::EQUAL],
                    Report::se_keys => [1.1],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::NOT_EQUAL],
                    Report::se_keys => [2.2],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::GREATER_THAN],
                    Report::se_keys => [1.1],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::GREATER_THAN_EQ],
                    Report::se_keys => [1.1],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::LOWER_THAN],
                    Report::se_keys => [1.1],
                ],
                []
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::LOWER_THAN_EQ],
                    Report::se_keys => [1.1],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** money ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::EQUAL],
                    Report::se_keys => [1.1],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::NOT_EQUAL],
                    Report::se_keys => [2.2],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::GREATER_THAN],
                    Report::se_keys => [1.1],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::GREATER_THAN_EQ],
                    Report::se_keys => [1.1],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::LOWER_THAN],
                    Report::se_keys => [1.1],
                ],
                []
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::LOWER_THAN_EQ],
                    Report::se_keys => [1.1],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** date ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date" => '2000-01-01'
                    ],
                    "TST_2" => [
                        "test_ddui_all__date" => '2020-01-01'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date"],
                    Report::se_funcs => [self::EQUAL],
                    Report::se_keys => ['2000-01-01'],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date" => '2000-01-01'
                    ],
                    "TST_2" => [
                        "test_ddui_all__date" => '2020-01-01'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date"],
                    Report::se_funcs => [self::NOT_EQUAL],
                    Report::se_keys => ['2020-01-01'],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date" => '2000-01-01'
                    ],
                    "TST_2" => [
                        "test_ddui_all__date" => '2020-01-01'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date"],
                    Report::se_funcs => [self::GREATER_THAN],
                    Report::se_keys => ['2000-01-01'],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date" => '2000-01-01'
                    ],
                    "TST_2" => [
                        "test_ddui_all__date" => '2020-01-01'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date"],
                    Report::se_funcs => [self::GREATER_THAN_EQ],
                    Report::se_keys => ['2000-01-01'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date" => '2000-01-01'
                    ],
                    "TST_2" => [
                        "test_ddui_all__date" => '2020-01-01'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date"],
                    Report::se_funcs => [self::LOWER_THAN],
                    Report::se_keys => ['2000-01-01'],
                ],
                []
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date" => '2000-01-01'
                    ],
                    "TST_2" => [
                        "test_ddui_all__date" => '2020-01-01'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date"],
                    Report::se_funcs => [self::LOWER_THAN_EQ],
                    Report::se_keys => ['2000-01-01'],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date" => '2000-01-01'
                    ],
                    "TST_2" => [
                        "test_ddui_all__date" => '2020-01-01'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date" => '2000-01-01'
                    ],
                    "TST_2" => [
                        "test_ddui_all__date" => '2020-01-01'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** time ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::EQUAL],
                    Report::se_keys => ['01:02:03'],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::NOT_EQUAL],
                    Report::se_keys => ['04:05:06'],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::GREATER_THAN],
                    Report::se_keys => ['01:02:03'],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::GREATER_THAN_EQ],
                    Report::se_keys => ['01:02:03'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::LOWER_THAN],
                    Report::se_keys => ['01:02:03'],
                ],
                []
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::LOWER_THAN_EQ],
                    Report::se_keys => ['01:02:03'],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** timestamp ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp"],
                    Report::se_funcs => [self::EQUAL],
                    Report::se_keys => ['2000-01-01 01:02:03'],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp"],
                    Report::se_funcs => [self::NOT_EQUAL],
                    Report::se_keys => ['2020-01-01 04:05:06'],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp"],
                    Report::se_funcs => [self::GREATER_THAN],
                    Report::se_keys => ['2000-01-01 01:02:03'],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp"],
                    Report::se_funcs => [self::GREATER_THAN_EQ],
                    Report::se_keys => ['2000-01-01 01:02:03'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp"],
                    Report::se_funcs => [self::LOWER_THAN],
                    Report::se_keys => ['2000-01-01 01:02:03'],
                ],
                []
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp"],
                    Report::se_funcs => [self::LOWER_THAN_EQ],
                    Report::se_keys => ['2000-01-01 01:02:03'],

                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /***************** color *********************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color" => "#000000"
                    ],
                    "TST_2" => [
                        "test_ddui_all__color" => "#FFFFFF"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color"],
                    Report::se_funcs => [self::EQUAL],
                    Report::se_keys => ["#000000"]
                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color" => "#000000"
                    ],
                    "TST_2" => [
                        "test_ddui_all__color" => "#FFFFFF"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color"],
                    Report::se_funcs => [self::NOT_EQUAL],
                    Report::se_keys => ["#000000"]
                ],
                [
                    "TST_0",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color" => "#000000"
                    ],
                    "TST_2" => [
                        "test_ddui_all__color" => "#FFFFFF"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color" => "#000000"
                    ],
                    "TST_2" => [
                        "test_ddui_all__color" => "#FFFFFF"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["#000000"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /***************** enum *********************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumlist" => "AD"
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumlist" => "FR"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumlist"],
                    Report::se_funcs => [self::EQUAL],
                    Report::se_keys => ["AD"]
                ],
                [
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumlist" => "AD"
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumlist" => "FR"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumlist"],
                    Report::se_funcs => [self::NOT_EQUAL],
                    Report::se_keys => ["AD"]
                ],
                [
                    "TST_0",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumlist" => "AD"
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumlist" => "FR"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumlist"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumlist" => "AD"
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumlist" => "FR"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumlist"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["AD"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /****************************************** TYPES MULTIPLES *****************************************************/
            /******************** text[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text_array" => [
                            "Un",
                            "Deux",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__text_array" => [
                            "Deux",
                            "Trois",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text_array"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => ["t"],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text_array" => [
                            "Un",
                            "Deux",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__text_array" => [
                            "Deux",
                            "Trois",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ["t"],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text_array" => [
                            "Un",
                            "Deux",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__text_array" => [
                            "Deux",
                            "Trois",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ["Deux"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text_array" => [
                            "Un",
                            "Deux",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__text_array" => [
                            "Deux",
                            "Trois",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text_array" => [
                            "Un",
                            "Deux",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__text_array" => [
                            "Deux",
                            "Trois",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** longtext[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext_array" => [
                            "Un\nDeux",
                            "Deux\nTrois",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext_array" => [
                            "Deux\nTrois",
                            "Trois\nQuatre",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext_array"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => ["qu"],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext_array" => [
                            "Un\nDeux",
                            "Deux\nTrois",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext_array" => [
                            "Deux\nTrois",
                            "Trois\nQuatre",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ["qu"],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext_array" => [
                            "Un\nDeux",
                            "Deux\nTrois",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext_array" => [
                            "Deux\nTrois",
                            "Trois\nQuatre",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ["Deux\nTrois"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext_array" => [
                            "Un\nDeux",
                            "Deux\nTrois",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext_array" => [
                            "Deux\nTrois",
                            "Trois\nQuatre",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext_array" => [
                            "Un\nDeux",
                            "Deux\nTrois",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext_array" => [
                            "Deux\nTrois",
                            "Trois\nQuatre",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** htmltext[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Un</b><em>Deux</em></p>",
                            "<p><b>Deux</b><em>Trois</em></p>",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Deux</b><em>Trois</em></p>",
                            "<p><b>Trois</b><em>Quatre</em></p>",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext_array"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => ["qu"],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Un</b><em>Deux</em></p>",
                            "<p><b>Deux</b><em>Trois</em></p>",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Deux</b><em>Trois</em></p>",
                            "<p><b>Trois</b><em>Quatre</em></p>",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ["qu"],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Un</b><em>Deux</em></p>",
                            "<p><b>Deux</b><em>Trois</em></p>",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Deux</b><em>Trois</em></p>",
                            "<p><b>Trois</b><em>Quatre</em></p>",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ["<p><b>Deux</b><em>Trois</em></p>"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Un</b><em>Deux</em></p>",
                            "<p><b>Deux</b><em>Trois</em></p>",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Deux</b><em>Trois</em></p>",
                            "<p><b>Trois</b><em>Quatre</em></p>",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Un</b><em>Deux</em></p>",
                            "<p><b>Deux</b><em>Trois</em></p>",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Deux</b><em>Trois</em></p>",
                            "<p><b>Trois</b><em>Quatre</em></p>",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /******************** int[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer_array" => [
                            1,
                            2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer_array" => [
                            2,
                            3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer_array"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => [3],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer_array" => [
                            1,
                            2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer_array" => [
                            2,
                            3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => [3],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer_array" => [
                            1,
                            2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer_array" => [
                            2,
                            3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => [2],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer_array" => [
                            1,
                            2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer_array" => [
                            2,
                            3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer_array" => [
                            1,
                            2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer_array" => [
                            2,
                            3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /******************** double[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__double_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double_array"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => [3.3],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__double_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => [3.3],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__double_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => [2.2],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__double_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__double_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /******************** money[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__money_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money_array"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => [3.3],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__money_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => [3.3],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__money_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => [2.2],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__money_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__money_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** date[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date_array" => [
                            '2000-01-01',
                            '2010-01-01',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__date_array" => [
                            '2010-01-01',
                            '2020-01-01',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date_array"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => ['2020-01-01'],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date_array" => [
                            '2000-01-01',
                            '2010-01-01',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__date_array" => [
                            '2010-01-01',
                            '2020-01-01',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ['2020-01-01'],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date_array" => [
                            '2000-01-01',
                            '2010-01-01',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__date_array" => [
                            '2010-01-01',
                            '2020-01-01',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ['2010-01-01'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date_array" => [
                            '2000-01-01',
                            '2010-01-01',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__date_array" => [
                            '2010-01-01',
                            '2020-01-01',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date_array" => [
                            '2000-01-01',
                            '2010-01-01',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__date_array" => [
                            '2010-01-01',
                            '2020-01-01',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** time[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time_array" => [
                            '01:02:03',
                            '04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__time_array" => [
                            '04:05:06',
                            '07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time_array"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => ['07:08:09'],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time_array" => [
                            '01:02:03',
                            '04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__time_array" => [
                            '04:05:06',
                            '07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ['07:08:09'],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time_array" => [
                            '01:02:03',
                            '04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__time_array" => [
                            '04:05:06',
                            '07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ['04:05:06'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time_array" => [
                            '01:02:03',
                            '04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__time_array" => [
                            '04:05:06',
                            '07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time_array" => [
                            '01:02:03',
                            '04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__time_array" => [
                            '04:05:06',
                            '07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /******************** timestamp[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp_array" => [
                            '2000-01-01 01:02:03',
                            '2010-01-01 04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp_array" => [
                            '2010-01-01 04:05:06',
                            '2020-01-01 07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp_array"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => ['2020-01-01 07:08:09'],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp_array" => [
                            '2000-01-01 01:02:03',
                            '2010-01-01 04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp_array" => [
                            '2010-01-01 04:05:06',
                            '2020-01-01 07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ['2020-01-01 07:08:09'],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp_array" => [
                            '2000-01-01 01:02:03',
                            '2010-01-01 04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp_array" => [
                            '2010-01-01 04:05:06',
                            '2020-01-01 07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ['2010-01-01 04:05:06'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp_array" => [
                            '2000-01-01 01:02:03',
                            '2010-01-01 04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp_array" => [
                            '2010-01-01 04:05:06',
                            '2020-01-01 07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp_array" => [
                            '2000-01-01 01:02:03',
                            '2010-01-01 04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp_array" => [
                            '2010-01-01 04:05:06',
                            '2020-01-01 07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /***************** color[] *********************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color_array" => ["#000000", "#111111"]
                    ],
                    "TST_2" => [
                        "test_ddui_all__color_array" => ["#111111", "#FFFFFF"]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color_array"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => ["1"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color_array" => ["#000000", "#111111"]
                    ],
                    "TST_2" => [
                        "test_ddui_all__color_array" => ["#111111", "#FFFFFF"]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ["FF"]
                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color_array" => ["#000000", "#111111"]
                    ],
                    "TST_2" => [
                        "test_ddui_all__color_array" => ["#111111", "#FFFFFF"]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ["#111111"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color_array" => ["#000000", "#111111"]
                    ],
                    "TST_2" => [
                        "test_ddui_all__color_array" => ["#111111", "#FFFFFF"]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color_array" => ["#000000", "#111111"]
                    ],
                    "TST_2" => [
                        "test_ddui_all__color_array" => ["#111111", "#FFFFFF"]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /******************** enum[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumslist" => [
                            'AD',
                            'FR',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumslist" => [
                            'FR',
                            'EN',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumslist"],
                    Report::se_funcs => [self::CONTAINS],
                    Report::se_keys => ['E'],

                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumslist" => [
                            'AD',
                            'FR',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumslist" => [
                            'FR',
                            'EN',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumslist"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ['EN'],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumslist" => [
                            'AD',
                            'FR',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumslist" => [
                            'FR',
                            'EN',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumslist"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ['FR'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumslist" => [
                            'AD',
                            'FR',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumslist" => [
                            'FR',
                            'EN',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumslist"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumslist" => [
                            'AD',
                            'FR',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumslist" => [
                            'FR',
                            'EN',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumslist"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

        ];
    }

    public function dataFieldEmptyValue()
    {
        return [
            /****************************************** TYPES SIMPLES *****************************************************/

            /**************************** text ****************************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::EQUAL],

                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::STARTS_WITH],

                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::NOT_EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::NOT_CONTAINS]
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["Un"],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text" => "Un"
                    ],
                    "TST_2" => [
                        "test_ddui_all__text" => "Deux"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["Un"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /************** longtext ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext" => "Un\nDeux"
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext" => "Deux\nTrois"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext"],
                    Report::se_funcs => [self::CONTAINS],

                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext" => "Un\nDeux"
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext" => "Deux\nTrois"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext"],
                    Report::se_funcs => [self::STARTS_WITH]

                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2"
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext" => "Un\nDeux"
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext" => "Deux\nTrois"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext"],
                    Report::se_funcs => [self::NOT_CONTAINS]
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext" => "Un\nDeux"
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext" => "Deux\nTrois"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["Un"],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext" => "Un\nDeux"
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext" => "Deux\nTrois"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["Deux"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** htmltext ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext" => "<p><b>Un</b><em>Deux</em></p>"
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext" => "<p><b>Deux</b><em>Trois</em></p>"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext"],
                    Report::se_funcs => [self::CONTAINS],

                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext" => "<p><b>Un</b><em>Deux</em></p>"
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext" => "<p><b>Deux</b><em>Trois</em></p>"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext"],
                    Report::se_funcs => [self::NOT_CONTAINS],

                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext" => "<p><b>Un</b><em>Deux</em></p>"
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext" => "<p><b>Deux</b><em>Trois</em></p>"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["Un"],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext" => "<p><b>Un</b><em>Deux</em></p>"
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext" => "<p><b>Deux</b><em>Trois</em></p>"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["Un"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** int ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::NOT_EQUAL]

                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::GREATER_THAN],

                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::GREATER_THAN_EQ],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::LOWER_THAN],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::LOWER_THAN_EQ],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [1],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer" => 1
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer" => 2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [2],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** double ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::NOT_EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::GREATER_THAN],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::GREATER_THAN_EQ],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::LOWER_THAN],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::LOWER_THAN_EQ],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [1.1],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__double" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [1.1],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** money ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::NOT_EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::GREATER_THAN],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::GREATER_THAN_EQ],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::LOWER_THAN],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::LOWER_THAN_EQ],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [1.1],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money" => 1.1
                    ],
                    "TST_2" => [
                        "test_ddui_all__money" => 2.2
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [2.1],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** date ******************/
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__date" => '2000-01-01'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__date" => '2020-01-01'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__date"],
//                    Report::se_funcs => [self::EQUAL],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__date" => '2000-01-01'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__date" => '2020-01-01'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__date"],
//                    Report::se_funcs => [self::NOT_EQUAL],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__date" => '2000-01-01'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__date" => '2020-01-01'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__date"],
//                    Report::se_funcs => [self::GREATER_THAN],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__date" => '2000-01-01'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__date" => '2020-01-01'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__date"],
//                    Report::se_funcs => [self::GREATER_THAN_EQ],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__date" => '2000-01-01'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__date" => '2020-01-01'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__date"],
//                    Report::se_funcs => [self::LOWER_THAN],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__date" => '2000-01-01'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__date" => '2020-01-01'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__date"],
//                    Report::se_funcs => [self::LOWER_THAN_EQ],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date" => '2000-01-01'
                    ],
                    "TST_2" => [
                        "test_ddui_all__date" => '2020-01-01'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ['2000-01-01'],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date" => '2000-01-01'
                    ],
                    "TST_2" => [
                        "test_ddui_all__date" => '2020-01-01'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ['2000-01-01'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** time ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::NOT_EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::GREATER_THAN],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::GREATER_THAN_EQ],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::LOWER_THAN],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::LOWER_THAN_EQ],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ['04:05:06'],
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time" => '01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__time" => '04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ['04:05:06'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** timestamp ******************/
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__timestamp"],
//                    Report::se_funcs => [self::EQUAL],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__timestamp"],
//                    Report::se_funcs => [self::NOT_EQUAL],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__timestamp"],
//                    Report::se_funcs => [self::GREATER_THAN],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__timestamp"],
//                    Report::se_funcs => [self::GREATER_THAN_EQ],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__timestamp"],
//                    Report::se_funcs => [self::LOWER_THAN],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__timestamp"],
//                    Report::se_funcs => [self::LOWER_THAN_EQ],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__timestamp"],
//                    Report::se_funcs => [self::IS_NULL],
//                    Report::se_keys => ['2020-01-01 04:05:06'],
//
//                ],
//                [
//                    "TST_0",
//                ]
//            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp" => '2000-01-01 01:02:03'
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp" => '2020-01-01 04:05:06'
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ['2020-01-01 04:05:06'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /***************** color *********************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color" => "#000000"
                    ],
                    "TST_2" => [
                        "test_ddui_all__color" => "#FFFFFF"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color"],
                    Report::se_funcs => [self::EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color" => "#000000"
                    ],
                    "TST_2" => [
                        "test_ddui_all__color" => "#FFFFFF"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color"],
                    Report::se_funcs => [self::NOT_EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color" => "#000000"
                    ],
                    "TST_2" => [
                        "test_ddui_all__color" => "#FFFFFF"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["#FFFFFF"]
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color" => "#000000"
                    ],
                    "TST_2" => [
                        "test_ddui_all__color" => "#FFFFFF"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["#000000"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /***************** enum *********************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumlist" => "AD"
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumlist" => "FR"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumlist"],
                    Report::se_funcs => [self::EQUAL]
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumlist" => "AD"
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumlist" => "FR"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumlist"],
                    Report::se_funcs => [self::NOT_EQUAL]
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumlist" => "AD"
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumlist" => "FR"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumlist"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["FR"]
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumlist" => "AD"
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumlist" => "FR"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumlist"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["AD"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /****************************************** TYPES MULTIPLES *****************************************************/

            /******************** text[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text_array" => [
                            "Un",
                            "Deux",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__text_array" => [
                            "Deux",
                            "Trois",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text_array" => [
                            "Un",
                            "Deux",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__text_array" => [
                            "Deux",
                            "Trois",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text_array" => [
                            "Un",
                            "Deux",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__text_array" => [
                            "Deux",
                            "Trois",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text_array" => [
                            "Un",
                            "Deux",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__text_array" => [
                            "Deux",
                            "Trois",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["Deux"],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__text_array" => [
                            "Un",
                            "Deux",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__text_array" => [
                            "Deux",
                            "Trois",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__text_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["Deux"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** longtext[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext_array" => [
                            "Un\nDeux",
                            "Deux\nTrois",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext_array" => [
                            "Deux\nTrois",
                            "Trois\nQuatre",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext_array" => [
                            "Un\nDeux",
                            "Deux\nTrois",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext_array" => [
                            "Deux\nTrois",
                            "Trois\nQuatre",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext_array" => [
                            "Un\nDeux",
                            "Deux\nTrois",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext_array" => [
                            "Deux\nTrois",
                            "Trois\nQuatre",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext_array" => [
                            "Un\nDeux",
                            "Deux\nTrois",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext_array" => [
                            "Deux\nTrois",
                            "Trois\nQuatre",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["Deux"],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__longtext_array" => [
                            "Un\nDeux",
                            "Deux\nTrois",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__longtext_array" => [
                            "Deux\nTrois",
                            "Trois\nQuatre",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__longtext_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["Deux"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** htmltext[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Un</b><em>Deux</em></p>",
                            "<p><b>Deux</b><em>Trois</em></p>",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Deux</b><em>Trois</em></p>",
                            "<p><b>Trois</b><em>Quatre</em></p>",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Un</b><em>Deux</em></p>",
                            "<p><b>Deux</b><em>Trois</em></p>",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Deux</b><em>Trois</em></p>",
                            "<p><b>Trois</b><em>Quatre</em></p>",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Un</b><em>Deux</em></p>",
                            "<p><b>Deux</b><em>Trois</em></p>",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Deux</b><em>Trois</em></p>",
                            "<p><b>Trois</b><em>Quatre</em></p>",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Un</b><em>Deux</em></p>",
                            "<p><b>Deux</b><em>Trois</em></p>",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Deux</b><em>Trois</em></p>",
                            "<p><b>Trois</b><em>Quatre</em></p>",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["Deux"],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Un</b><em>Deux</em></p>",
                            "<p><b>Deux</b><em>Trois</em></p>",
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__htmltext_array" => [
                            "<p><b>Deux</b><em>Trois</em></p>",
                            "<p><b>Trois</b><em>Quatre</em></p>",
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__htmltext_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["Deux"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /******************** int[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer_array" => [
                            1,
                            2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer_array" => [
                            2,
                            3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer_array" => [
                            1,
                            2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer_array" => [
                            2,
                            3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer_array" => [
                            1,
                            2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer_array" => [
                            2,
                            3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer_array" => [
                            1,
                            2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer_array" => [
                            2,
                            3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [2],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__integer_array" => [
                            1,
                            2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__integer_array" => [
                            2,
                            3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__integer_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [2],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /******************** double[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__double_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__double_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__double_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__double_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [2.2],
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__double_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__double_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__double_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [2.2],
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /******************** money[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__money_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__money_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__money_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__money_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [2.2],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__money_array" => [
                            1.1,
                            2.2,
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__money_array" => [
                            2.2,
                            3.3,
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__money_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [2.2],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** date[] *****************************/
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__date_array" => [
//                            '2000-01-01',
//                            '2010-01-01',
//                        ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__date_array" => [
//                            '2010-01-01',
//                            '2020-01-01',
//                        ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__date_array"],
//                    Report::se_funcs => [self::CONTAINS],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__date_array" => [
//                            '2000-01-01',
//                            '2010-01-01',
//                        ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__date_array" => [
//                            '2010-01-01',
//                            '2020-01-01',
//                        ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__date_array"],
//                    Report::se_funcs => [self::NOT_CONTAINS],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__date_array" => [
//                            '2000-01-01',
//                            '2010-01-01',
//                        ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__date_array" => [
//                            '2010-01-01',
//                            '2020-01-01',
//                        ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__date_array"],
//                    Report::se_funcs => [self::ONE_EQUALS],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date_array" => [
                            '2000-01-01',
                            '2010-01-01',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__date_array" => [
                            '2010-01-01',
                            '2020-01-01',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ['2020-01-01'],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__date_array" => [
                            '2000-01-01',
                            '2010-01-01',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__date_array" => [
                            '2010-01-01',
                            '2020-01-01',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__date_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ['2020-01-01'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** time[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time_array" => [
                            '01:02:03',
                            '04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__time_array" => [
                            '04:05:06',
                            '07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time_array" => [
                            '01:02:03',
                            '04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__time_array" => [
                            '04:05:06',
                            '07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time_array" => [
                            '01:02:03',
                            '04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__time_array" => [
                            '04:05:06',
                            '07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time_array" => [
                            '01:02:03',
                            '04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__time_array" => [
                            '04:05:06',
                            '07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ['07:08:09'],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__time_array" => [
                            '01:02:03',
                            '04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__time_array" => [
                            '04:05:06',
                            '07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__time_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ['07:08:09'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /******************** timestamp[] *****************************/
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__timestamp_array" => [
//                            '2000-01-01 01:02:03',
//                            '2010-01-01 04:05:06',
//                        ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__timestamp_array" => [
//                            '2010-01-01 04:05:06',
//                            '2020-01-01 07:08:09',
//                        ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__timestamp_array"],
//                    Report::se_funcs => [self::CONTAINS],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__timestamp_array" => [
//                            '2000-01-01 01:02:03',
//                            '2010-01-01 04:05:06',
//                        ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__timestamp_array" => [
//                            '2010-01-01 04:05:06',
//                            '2020-01-01 07:08:09',
//                        ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__timestamp_array"],
//                    Report::se_funcs => [self::NOT_CONTAINS],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__timestamp_array" => [
//                            '2000-01-01 01:02:03',
//                            '2010-01-01 04:05:06',
//                        ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__timestamp_array" => [
//                            '2010-01-01 04:05:06',
//                            '2020-01-01 07:08:09',
//                        ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__timestamp_array"],
//                    Report::se_funcs => [self::ONE_EQUALS],
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__timestamp_array" => [
//                            '2000-01-01 01:02:03',
//                            '2010-01-01 04:05:06',
//                        ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__timestamp_array" => [
//                            '2010-01-01 04:05:06',
//                            '2020-01-01 07:08:09',
//                        ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__timestamp_array"],
//                    Report::se_funcs => [self::IS_NULL],
//                    Report::se_keys => ['2020-01-01 07:08:09'],
//
//                ],
//                [
//                    "TST_0",
//                ]
//            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__timestamp_array" => [
                            '2000-01-01 01:02:03',
                            '2010-01-01 04:05:06',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__timestamp_array" => [
                            '2010-01-01 04:05:06',
                            '2020-01-01 07:08:09',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__timestamp_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ['2020-01-01 07:08:09'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /***************** color[] *********************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color_array" => ["#000000", "#111111"]
                    ],
                    "TST_2" => [
                        "test_ddui_all__color_array" => ["#111111", "#FFFFFF"]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color_array" => ["#000000", "#111111"]
                    ],
                    "TST_2" => [
                        "test_ddui_all__color_array" => ["#111111", "#FFFFFF"]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color_array" => ["#000000", "#111111"]
                    ],
                    "TST_2" => [
                        "test_ddui_all__color_array" => ["#111111", "#FFFFFF"]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color_array" => ["#000000", "#111111"]
                    ],
                    "TST_2" => [
                        "test_ddui_all__color_array" => ["#111111", "#FFFFFF"]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["#111111"]
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__color_array" => ["#000000", "#111111"]
                    ],
                    "TST_2" => [
                        "test_ddui_all__color_array" => ["#111111", "#FFFFFF"]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__color_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["#111111"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],


            /******************** enum[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumslist" => [
                            'AD',
                            'FR',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumslist" => [
                            'FR',
                            'EN',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumslist"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumslist" => [
                            'AD',
                            'FR',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumslist" => [
                            'FR',
                            'EN',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumslist"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumslist" => [
                            'AD',
                            'FR',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumslist" => [
                            'FR',
                            'EN',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumslist"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumslist" => [
                            'AD',
                            'FR',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumslist" => [
                            'FR',
                            'EN',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumslist"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ['FR'],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__enumslist" => [
                            'AD',
                            'FR',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__enumslist" => [
                            'FR',
                            'EN',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__enumslist"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ['FR'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

        ];
    }


    /**
     * @dataProvider dataFieldRelations
     * @dataProvider dataFieldEmptyRelations
     * @param array $smartElementToFind
     * @param array $reportData
     * @param array $expectedSEFound
     * @param string $skippedTestMessage
     */
    public function testFieldRelations(
        array $smartElementToFind,
        array $reportData,
        array $expectedSEFound,
        string $skippedTestMessage = ""
    ) {
        $seProcess = function ($elt, $id, $value) {
            if (is_array($value)) {
                $newValues = array_map(function (&$name) {
                    if (is_array($name)) {
                        return array_map(function ($subName) {
                            return self::$idMap[$subName];
                        }, $name);
                    } else {
                        return self::$idMap[$name];
                    }
                }, $value);
                $elt->setAttributeValue($id, $newValues);
            } else {
                $elt->setAttributeValue($id, self::$idMap[$value]);
            }
        };

        $reportProcess = function ($report, $id, $value) {
            if ($id === Report::se_keys) {
                $newValues = array_map(function ($name) {
                    if (in_array($name, self::RELATIONS_NAME)) {
                        return self::$idMap[$name];
                    } else {
                        return $name;
                    }
                }, $value);
                $report->setAttributeValue($id, $newValues);
            } else {
                $report->setAttributeValue($id, $value);
            }
        };

        $this->genericTest(
            $smartElementToFind,
            $reportData,
            $expectedSEFound,
            $seProcess,
            $reportProcess,
            $skippedTestMessage
        );
    }

    public function dataFieldRelations()
    {
        return [
            /************** docid ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid" => "FOO_1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid" => "FOO_2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid"],
                    Report::se_funcs => [self::EQUAL],
                    Report::se_keys => ["FOO_2"]
                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid" => "FOO_1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid" => "FOO_2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid"],
                    Report::se_funcs => [self::NOT_EQUAL],
                    Report::se_keys => ["FOO_2"]
                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            /**[
             * [
             * "TST_0" => [],
             * "TST_1" => [
             * "test_ddui_all__docid" => "FOO_1"
             * ],
             * "TST_2" => [
             * "test_ddui_all__docid" => "FOO_2"
             * ],
             * ],
             * [
             * Report::se_attrids => ["test_ddui_all__docid"],
             * Report::se_funcs => [self::TITLE_CONTAINS],
             * Report::se_keys => ["2"]
             * ],
             * [
             * "TST_2",
             * ]
             * ],**/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid" => "FOO_1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid" => "FOO_2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid" => "FOO_1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid" => "FOO_2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["FOO_2"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /*************** account ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account" => "ACCOUNT 1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__account" => "ACCOUNT 2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account"],
                    Report::se_funcs => [self::EQUAL],
                    Report::se_keys => ["ACCOUNT 2"]
                ],
                [
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account" => "ACCOUNT 1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__account" => "ACCOUNT 2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account"],
                    Report::se_funcs => [self::NOT_EQUAL],
                    Report::se_keys => ["ACCOUNT 2"]
                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account" => "ACCOUNT 1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__account" => "ACCOUNT 2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account"],
                    Report::se_funcs => [self::TITLE_CONTAINS],
                    Report::se_keys => ["CC"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account" => "ACCOUNT 1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__account" => "ACCOUNT 2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account" => "ACCOUNT 1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__account" => "ACCOUNT 2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["ACCOUNT 2"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** docid[] *****************************/
            /**[
             * [
             * "TST_0" => [],
             * "TST_1" => [
             * "test_ddui_all__docid_multiple" => [
             * 'FOO_1',
             * 'FOO_2',
             * ]
             * ],
             * "TST_2" => [
             * "test_ddui_all__docid_multiple" => [
             * 'FOO_2',
             * 'FOO_3',
             * ]
             * ],
             * ],
             * [
             * Report::se_attrids => ["test_ddui_all__docid_multiple"],
             * Report::se_funcs => [self::CONTAINS],
             * Report::se_keys => ['O_3'],
             *
             * ],
             * [
             * "TST_2",
             * ]
             * ], **/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_1',
                            'FOO_2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_2',
                            'FOO_3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ['FOO_3'],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_1',
                            'FOO_2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_2',
                            'FOO_3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ['FOO_2'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_1',
                            'FOO_2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_2',
                            'FOO_3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_1',
                            'FOO_2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_2',
                            'FOO_3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** account[] *****************************/
            /**[
             * [
             * "TST_0" => [],
             * "TST_1" => [
             * "test_ddui_all__account_multiple" => [
             * 'ACCOUNT 1',
             * 'ACCOUNT 2',
             * ]
             * ],
             * "TST_2" => [
             * "test_ddui_all__account_multiple" => [
             * 'ACCOUNT 2',
             * 'ACCOUNT 3',
             * ]
             * ],
             * ],
             * [
             * Report::se_attrids => ["test_ddui_all__account_multiple"],
             * Report::se_funcs => [self::CONTAINS],
             * Report::se_keys => ['3'],
             *
             * ],
             * [
             * "TST_2",
             * ]
             * ], **/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 1',
                            'ACCOUNT 2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 2',
                            'ACCOUNT 3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ['ACCOUNT 3'],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 1',
                            'ACCOUNT 2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 2',
                            'ACCOUNT 3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ['ACCOUNT 2'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 1',
                            'ACCOUNT 2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 2',
                            'ACCOUNT 3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 1',
                            'ACCOUNT 2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 2',
                            'ACCOUNT 3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** docid[][] *****************************/
            /**[
             * [
             * "TST_0" => [],
             * "TST_1" => [
             * "test_ddui_all__docid_multiple_array" => [
             * ['FOO_1','FOO_2'],
             * ['FOO_2','FOO_3'],
             * ]
             * ],
             * "TST_2" => [
             * "test_ddui_all__docid_multiple_array" => [
             * ['FOO_2','FOO_3'],
             * ['FOO_3','FOO_4'],
             * ]
             * ],
             * ],
             * [
             * Report::se_attrids => ["test_ddui_all__docid_multiple_array"],
             * Report::se_funcs => [self::CONTAINS],
             * Report::se_keys => ['O_3'],
             *
             * ],
             * [
             * "TST_2",
             * ]
             * ], **/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_1', 'FOO_2'],
                            ['FOO_2', 'FOO_3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_2', 'FOO_3'],
                            ['FOO_3', 'FOO_4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                    Report::se_keys => ['FOO_4'],

                ],
                [
                    "TST_0",
                    "TST_1",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_1', 'FOO_2'],
                            ['FOO_2', 'FOO_3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_2', 'FOO_3'],
                            ['FOO_3', 'FOO_4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ['FOO_2'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_1', 'FOO_2'],
                            ['FOO_2', 'FOO_3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_2', 'FOO_3'],
                            ['FOO_3', 'FOO_4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_1', 'FOO_2'],
                            ['FOO_2', 'FOO_3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_2', 'FOO_3'],
                            ['FOO_3', 'FOO_4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** account[][] *****************************/
            /**[
             * [
             * "TST_0" => [],
             * "TST_1" => [
             * "test_ddui_all__account_multiple_array" => [
             * ['ACCOUNT 1','ACCOUNT 2'],
             * ['ACCOUNT 2','ACCOUNT 3'],
             * ]
             * ],
             * "TST_2" => [
             * "test_ddui_all__account_multiple_array" => [
             * ['ACCOUNT 2','ACCOUNT 3'],
             * ['ACCOUNT 3','ACCOUNT 4'],
             * ]
             * ],
             * ],
             * [
             * Report::se_attrids => ["test_ddui_all__account_multiple_array"],
             * Report::se_funcs => [self::CONTAINS],
             * Report::se_keys => ['O_3'],
             *
             * ],
             * [
             * "TST_2",
             * ]
             * ], **/
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__account_multiple_array" => [
//                            ['ACCOUNT 1','ACCOUNT 2'],
//                            ['ACCOUNT 2','ACCOUNT 3'],
//                        ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__account_multiple_array" => [
//                            ['ACCOUNT 2','ACCOUNT 3'],
//                            ['ACCOUNT 3','ACCOUNT 4'],
//                        ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__account_multiple_array"],
//                    Report::se_funcs => [self::NOT_CONTAINS],
//                    Report::se_keys => ['ACCOUNT 4'],
//
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                ],
//                true
//            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 1', 'ACCOUNT 2'],
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                            ['ACCOUNT 3', 'ACCOUNT 4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                    Report::se_keys => ['ACCOUNT 2'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 1', 'ACCOUNT 2'],
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                            ['ACCOUNT 3', 'ACCOUNT 4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 1', 'ACCOUNT 2'],
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                            ['ACCOUNT 3', 'ACCOUNT 4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

        ];
    }

    public function dataFieldEmptyRelations()
    {
        return [
            /************** docid ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid" => "FOO_1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid" => "FOO_2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid"],
                    Report::se_funcs => [self::EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ],
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid" => "FOO_1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid" => "FOO_2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid"],
                    Report::se_funcs => [self::NOT_EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid" => "FOO_1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid" => "FOO_2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid"],
                    Report::se_funcs => [self::TITLE_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid" => "FOO_1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid" => "FOO_2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["FOO_2"]
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid" => "FOO_1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid" => "FOO_2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["FOO_2"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /*************** account ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account" => "ACCOUNT 1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__account" => "ACCOUNT 2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account"],
                    Report::se_funcs => [self::EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account" => "ACCOUNT 1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__account" => "ACCOUNT 2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account"],
                    Report::se_funcs => [self::NOT_EQUAL],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account" => "ACCOUNT 1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__account" => "ACCOUNT 2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account"],
                    Report::se_funcs => [self::TITLE_CONTAINS]
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account" => "ACCOUNT 1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__account" => "ACCOUNT 2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["ACCOUNT 1"]
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account" => "ACCOUNT 1"
                    ],
                    "TST_2" => [
                        "test_ddui_all__account" => "ACCOUNT 2"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["ACCOUNT 2"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** docid[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_1',
                            'FOO_2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_2',
                            'FOO_3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ],
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_1',
                            'FOO_2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_2',
                            'FOO_3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_1',
                            'FOO_2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_2',
                            'FOO_3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_1',
                            'FOO_2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_2',
                            'FOO_3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ["ACCOUNT 1"],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_1',
                            'FOO_2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple" => [
                            'FOO_2',
                            'FOO_3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ["ACCOUNT 1"],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** account[] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 1',
                            'ACCOUNT 2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 2',
                            'ACCOUNT 3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 1',
                            'ACCOUNT 2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 2',
                            'ACCOUNT 3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 1',
                            'ACCOUNT 2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 2',
                            'ACCOUNT 3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 1',
                            'ACCOUNT 2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 2',
                            'ACCOUNT 3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ['ACCOUNT 2'],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 1',
                            'ACCOUNT 2',
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple" => [
                            'ACCOUNT 2',
                            'ACCOUNT 3',
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ['ACCOUNT 2'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** docid[][] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_1', 'FOO_2'],
                            ['FOO_2', 'FOO_3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_2', 'FOO_3'],
                            ['FOO_3', 'FOO_4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_1', 'FOO_2'],
                            ['FOO_2', 'FOO_3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_2', 'FOO_3'],
                            ['FOO_3', 'FOO_4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_1', 'FOO_2'],
                            ['FOO_2', 'FOO_3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_2', 'FOO_3'],
                            ['FOO_3', 'FOO_4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_1', 'FOO_2'],
                            ['FOO_2', 'FOO_3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_2', 'FOO_3'],
                            ['FOO_3', 'FOO_4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ['FOO_1'],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_1', 'FOO_2'],
                            ['FOO_2', 'FOO_3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__docid_multiple_array" => [
                            ['FOO_2', 'FOO_3'],
                            ['FOO_3', 'FOO_4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__docid_multiple_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ['FOO_1'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /******************** account[][] *****************************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 1', 'ACCOUNT 2'],
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                            ['ACCOUNT 3', 'ACCOUNT 4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 1', 'ACCOUNT 2'],
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                            ['ACCOUNT 3', 'ACCOUNT 4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ],
                true
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 1', 'ACCOUNT 2'],
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                            ['ACCOUNT 3', 'ACCOUNT 4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 1', 'ACCOUNT 2'],
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                            ['ACCOUNT 3', 'ACCOUNT 4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => ['ACCOUNT 2'],

                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 1', 'ACCOUNT 2'],
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__account_multiple_array" => [
                            ['ACCOUNT 2', 'ACCOUNT 3'],
                            ['ACCOUNT 3', 'ACCOUNT 4'],
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__account_multiple_array"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => ['ACCOUNT 2'],

                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

        ];
    }

    /**
     * @dataProvider dataFieldFiles
     * @dataProvider dataFieldEmptyFiles
     * @param array $smartElementToFind
     * @param array $reportData
     * @param array $expectedSEFound
     * @param string $skippedTestMessage
     */
    public function testFieldFiles(
        array $smartElementToFind,
        array $reportData,
        array $expectedSEFound,
        string $skippedTestMessage = ""
    ) {

        $seProcess = function ($elt, $id, $value) {
            if (is_array($value)) {
                foreach ($value as $index => $filename) {
                    $elt->setFile($id, $filename, "", $index);
                }
            } else {
                $elt->setFile($id, $value);
            }
        };

        $reportProcess = function ($report, $id, $value) {
            $report->setAttributeValue($id, $value);
        };

        $this->genericTest(
            $smartElementToFind,
            $reportData,
            $expectedSEFound,
            $seProcess,
            $reportProcess,
            $skippedTestMessage
        );
    }

    public function dataFieldFiles()
    {
        return [
            /************** file ******************/
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__file" => __DIR__. "/Inputs/dummy_file.txt"
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__file" => __DIR__. "/Inputs/dummy_file2.txt"
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__file"],
//                    Report::se_funcs => [self::CONTAINS],
//                    Report::se_keys => ["2"]
//                ],
//                [
//                    "TST_2",
//                ]
//            ],
//          TODO:  [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__file" => __DIR__. "/Inputs/dummy_file.txt"
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__file" => __DIR__. "/Inputs/dummy_file2.txt"
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__file"],
//                    Report::se_funcs => [self::NOT_CONTAINS],
//                    Report::se_keys => ["2"]
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                ]
//            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file.txt"
                    ],
                    "TST_2" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file2.txt"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file.txt"
                    ],
                    "TST_2" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file2.txt"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** image ******************/
//            TODO:[
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__image" => __DIR__. "/Inputs/dummy_image.jpeg"
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__image" => __DIR__. "/Inputs/dummy_image2.png"
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__image"],
//                    Report::se_funcs => [self::CONTAINS],
//                    Report::se_keys => ["2"]
//                ],
//                [
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__image" => __DIR__. "/Inputs/dummy_image.jpeg"
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__image" => __DIR__. "/Inputs/dummy_image2.png"
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__image"],
//                    Report::se_funcs => [self::NOT_CONTAINS],
//                    Report::se_keys => ["2"]
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                ]
//            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image.jpeg"
                    ],
                    "TST_2" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image2.png"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image.jpeg"
                    ],
                    "TST_2" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image2.png"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** file[] ******************/
//            TODO: [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__file_array" => [ __DIR__. "/Inputs/dummy_file.txt", __DIR__. "/Inputs/dummy_file2.txt" ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__file_array" => [ __DIR__. "/Inputs/dummy_file2.txt", __DIR__. "/Inputs/dummy_file3.txt" ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__file_array"],
//                    Report::se_funcs => [self::CONTAINS],
//                    Report::se_keys => ["3"]
//                ],
//                [
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__file_array" => [ __DIR__. "/Inputs/dummy_file.txt", __DIR__. "/Inputs/dummy_file2.txt" ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__file_array" => [ __DIR__. "/Inputs/dummy_file2.txt", __DIR__. "/Inputs/dummy_file3.txt" ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__file_array"],
//                    Report::se_funcs => [self::NOT_CONTAINS],
//                    Report::se_keys => ["3"]
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__file_array" => [ __DIR__. "/Inputs/dummy_file.txt", __DIR__. "/Inputs/dummy_file2.txt" ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__file_array" => [ __DIR__. "/Inputs/dummy_file2.txt", __DIR__. "/Inputs/dummy_file3.txt" ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__file_array"],
//                    Report::se_funcs => [self::ONE_EQUALS],
//                    Report::se_keys => [__DIR__. "/Inputs/dummy_file.txt"]
//                ],
//                [
//                    "TST_1",
//                ]
//            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file.txt",
                            __DIR__ . "/Inputs/dummy_file2.txt"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file2.txt",
                            __DIR__ . "/Inputs/dummy_file3.txt"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file.txt",
                            __DIR__ . "/Inputs/dummy_file2.txt"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file2.txt",
                            __DIR__ . "/Inputs/dummy_file3.txt"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_0",
                ]
            ],

            /************** image[] ******************/
//            TODO: [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__image_array" => [ __DIR__. "/Inputs/dummy_image.jpeg", __DIR__. "/Inputs/dummy_image2.png" ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__image_array" => [ __DIR__. "/Inputs/dummy_image2.png", __DIR__. "/Inputs/dummy_image3.jpeg" ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__image_array"],
//                    Report::se_funcs => [self::CONTAINS],
//                    Report::se_keys => ["3"]
//                ],
//                [
//                    "TST_2",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__image_array" => [ __DIR__. "/Inputs/dummy_image.jpeg", __DIR__. "/Inputs/dummy_image2.png" ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__image_array" => [ __DIR__. "/Inputs/dummy_image2.png", __DIR__. "/Inputs/dummy_image3.jpeg" ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__image_array"],
//                    Report::se_funcs => [self::NOT_CONTAINS],
//                    Report::se_keys => ["3"]
//                ],
//                [
//                    "TST_0",
//                    "TST_1",
//                ]
//            ],
//            [
//                [
//                    "TST_0" => [],
//                    "TST_1" => [
//                        "test_ddui_all__image_array" => [ __DIR__. "/Inputs/dummy_image.jpeg", __DIR__. "/Inputs/dummy_image2.png" ]
//                    ],
//                    "TST_2" => [
//                        "test_ddui_all__image_array" => [ __DIR__. "/Inputs/dummy_image2.png", __DIR__. "/Inputs/dummy_image3.jpeg" ]
//                    ],
//                ],
//                [
//                    Report::se_attrids => ["test_ddui_all__image_array"],
//                    Report::se_funcs => [self::ONE_EQUALS],
//                    Report::se_keys => [__DIR__. "/Inputs/dummy_image.jpeg"]
//                ],
//                [
//                    "TST_1",
//                ]
//            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image.jpeg",
                            __DIR__ . "/Inputs/dummy_image2.png"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image2.png",
                            __DIR__ . "/Inputs/dummy_image3.jpeg"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image.jpeg",
                            __DIR__ . "/Inputs/dummy_image2.png"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image2.png",
                            __DIR__ . "/Inputs/dummy_image3.jpeg"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => []
                ],
                [
                    "TST_0",
                ]
            ],


        ];
    }

    public function dataFieldEmptyFiles()
    {
        return [
            /************** file ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file.txt"
                    ],
                    "TST_2" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file2.txt"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file.txt"
                    ],
                    "TST_2" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file2.txt"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file.txt"
                    ],
                    "TST_2" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file2.txt"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [__DIR__ . "/Inputs/dummy_file2.txt"]
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file.txt"
                    ],
                    "TST_2" => [
                        "test_ddui_all__file" => __DIR__ . "/Inputs/dummy_file2.txt"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [__DIR__ . "/Inputs/dummy_file2.txt"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** image ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image.jpeg"
                    ],
                    "TST_2" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image2.png"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image.jpeg"
                    ],
                    "TST_2" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image2.png"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image.jpeg"
                    ],
                    "TST_2" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image2.png"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [__DIR__ . "/Inputs/dummy_image2.png"]
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image.jpeg"
                    ],
                    "TST_2" => [
                        "test_ddui_all__image" => __DIR__ . "/Inputs/dummy_image2.png"
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image"],
                    Report::se_funcs => [self::IS_NOT_NULL],
                    Report::se_keys => [__DIR__ . "/Inputs/dummy_image2.png"]
                ],
                [
                    "TST_1",
                    "TST_2",
                ]
            ],

            /************** file[] ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file.txt",
                            __DIR__ . "/Inputs/dummy_file2.txt"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file2.txt",
                            __DIR__ . "/Inputs/dummy_file3.txt"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file.txt",
                            __DIR__ . "/Inputs/dummy_file2.txt"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file2.txt",
                            __DIR__ . "/Inputs/dummy_file3.txt"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file.txt",
                            __DIR__ . "/Inputs/dummy_file2.txt"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file2.txt",
                            __DIR__ . "/Inputs/dummy_file3.txt"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file.txt",
                            __DIR__ . "/Inputs/dummy_file2.txt"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file2.txt",
                            __DIR__ . "/Inputs/dummy_file3.txt"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [__DIR__ . "/Inputs/dummy_file2.txt"]
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file.txt",
                            __DIR__ . "/Inputs/dummy_file2.txt"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__file_array" => [
                            __DIR__ . "/Inputs/dummy_file2.txt",
                            __DIR__ . "/Inputs/dummy_file3.txt"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__file_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [__DIR__ . "/Inputs/dummy_file2.txt"]
                ],
                [
                    "TST_0",
                ]
            ],

            /************** image[] ******************/
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image.jpeg",
                            __DIR__ . "/Inputs/dummy_image2.png"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image2.png",
                            __DIR__ . "/Inputs/dummy_image3.jpeg"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image_array"],
                    Report::se_funcs => [self::CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image.jpeg",
                            __DIR__ . "/Inputs/dummy_image2.png"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image2.png",
                            __DIR__ . "/Inputs/dummy_image3.jpeg"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image_array"],
                    Report::se_funcs => [self::NOT_CONTAINS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image.jpeg",
                            __DIR__ . "/Inputs/dummy_image2.png"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image2.png",
                            __DIR__ . "/Inputs/dummy_image3.jpeg"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image_array"],
                    Report::se_funcs => [self::ONE_EQUALS],
                ],
                [
                    "TST_0",
                    "TST_1",
                    "TST_2",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image.jpeg",
                            __DIR__ . "/Inputs/dummy_image2.png"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image2.png",
                            __DIR__ . "/Inputs/dummy_image3.jpeg"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [__DIR__ . "/Inputs/dummy_image.jpeg"]
                ],
                [
                    "TST_0",
                ]
            ],
            [
                [
                    "TST_0" => [],
                    "TST_1" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image.jpeg",
                            __DIR__ . "/Inputs/dummy_image2.png"
                        ]
                    ],
                    "TST_2" => [
                        "test_ddui_all__image_array" => [
                            __DIR__ . "/Inputs/dummy_image2.png",
                            __DIR__ . "/Inputs/dummy_image3.jpeg"
                        ]
                    ],
                ],
                [
                    Report::se_attrids => ["test_ddui_all__image_array"],
                    Report::se_funcs => [self::IS_NULL],
                    Report::se_keys => [__DIR__ . "/Inputs/dummy_image.jpeg"]
                ],
                [
                    "TST_0",
                ]
            ],


        ];
    }

    //endregion
}
