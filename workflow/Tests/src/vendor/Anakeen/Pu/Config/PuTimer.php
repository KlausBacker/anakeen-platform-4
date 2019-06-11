<?php

namespace Anakeen\Pu\Config;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\TimerManager;
use SmartStructure\Fields\Timer as TimerField;
use SmartStructure\Timer;

class PuTimer extends TestCaseWorkflowConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importWorkflowConfiguration(__DIR__ . "/Inputs/tst_I002.struct.xml");
        self::importWorkflowConfiguration(__DIR__ . "/Inputs/tst_W002.struct.xml");
        self::importWorkflowConfiguration(__DIR__ . "/Inputs/tst_M002.mail.xml");
        self::importWorkflowConfiguration(__DIR__ . "/Inputs/tst_T002.timer.xml");
    }

    /**
     * Test Field definition import
     *
     * @dataProvider dataTimerData
     *
     * @param string $timerName
     * @param array  $expectedFieldValues
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function testTimerData($timerName, array $expectedFieldValues)
    {
        $timer = SEManager::getDocument($timerName);
        $this->assertNotEmpty($timer, "Structure $timerName not found");

        //var_export($timer->getValues());
        foreach ($expectedFieldValues as $id => $expectedValue) {
            $expectedValue = preg_replace_callback("/(#[A-Z0-9_]+)/", function ($n) {
                return SEManager::getIdFromName(substr($n[0], 1));
            }, $expectedValue);
            $this->assertEquals($expectedValue, $timer->getRawValue($id));
        }
    }


    protected function verifyTimerAttach(SmartElement $elt, array $expectedTasks)
    {


        $tasks = TimerManager::getElementTasks($elt);
        $this->assertEquals(count($expectedTasks), count($tasks), "Task number error");


        foreach ($expectedTasks as $taskId => $expectedTask) {
            foreach ($expectedTask as $k => $expectedValue) {
                $expectedValue = self::replaceName($expectedValue);

                $this->assertEquals($expectedValue, $tasks[$taskId]->$k, "Error #$taskId - $k");
            }
        }
    }

    /**
     * @dataProvider dataTimerAttach
     * @param       $timerName
     * @param       $refDate
     * @param array $expectedTasks
     * @throws \Anakeen\Core\DocManager\Exception
     * @depends      testTimerData
     */
    public function testTimerAttach($timerName, $refDate, array $expectedTasks)
    {
        /** @var Timer $timer */
        $timer = SEManager::getDocument($timerName);
        $this->assertNotEmpty($timer, "Timer $timerName not found");
        $elt = SEManager::createDocument($timer->getRawValue(TimerField::tm_family));
        $this->assertNotEmpty($elt);
        $elt->setTitle("Test $timerName");
        $elt->setValue($timer->getRawValue(TimerField::tm_dyndate), $refDate);
        $elt->store();

        $eltId = $elt->id;
        $this->assertGreaterThan(1000, $eltId);

        $err = $elt->attachTimer($timer);
        $this->assertEmpty($err, "Cannot attach timer : $err");

        $this->verifyTimerAttach($elt, $expectedTasks);
    }


    /**
     * @dataProvider dataTimerAttachAgain
     * @param       $timerName
     * @param       $refDate
     * @param array $expectedTasks
     * @param       $refDate2
     * @param array $expectedTasks2
     * @throws \Anakeen\Core\DocManager\Exception
     * @depends      testTimerAttach
     */
    public function testTimerAttachAgain($timerName, $refDate, array $expectedTasks, $refDate2, array $expectedTasks2)
    {
        /** @var Timer $timer */
        $timer = SEManager::getDocument($timerName);
        $this->assertNotEmpty($timer, "Timer $timerName not found");
        $elt = SEManager::createDocument($timer->getRawValue(TimerField::tm_family));
        $this->assertNotEmpty($elt);
        $elt->setTitle("Test $timerName");
        $elt->setValue($timer->getRawValue(TimerField::tm_dyndate), $refDate);
        $elt->store();

        $eltId = $elt->id;
        $this->assertGreaterThan(1000, $eltId);

        $err = $elt->attachTimer($timer);
        $this->assertEmpty($err, "Cannot attach timer : $err");

        $this->verifyTimerAttach($elt, $expectedTasks);

        $elt->setValue($timer->getRawValue(TimerField::tm_dyndate), $refDate2);
        $elt->store();
        $this->verifyTimerAttach($elt, $expectedTasks2);
    }


    protected static function replaceName($n)
    {
        if (is_array($n)) {
            return array_map(function ($item) {
                return self::replaceName($item);
            }, $n);
        } else {
            return preg_replace_callback("/(#[A-Z0-9_]+)/i", function ($n) {
                return SEManager::getIdFromName(substr($n[0], 1));
            }, $n);
        }
    }

    //region Test Data providers

    public function dataTimerAttachAgain()
    {

        return [
            [
                "tid" => "TST_T002",
                "refdate1" => "2018-06-25 03:00",
                "tasks1" => array(
                    [
                        "timerid" => "#TST_T002",
                        "title" => "Test TST_T002",
                        "referencedate" => "2018-06-27 06:00:00", // 2 days 3 hours
                        "tododate" => "2018-06-29 08:00:00", // 2 days 2 hours
                        "actions" => [
                            "state" => "",
                            "tmail" => ["#TST_M002_E2E1", "#TST_M002_E2E3"],
                            "method" => ""

                        ]
                    ],
                    [
                        "timerid" => "#TST_T002",
                        "title" => "Test TST_T002",
                        "referencedate" => "2018-06-27 06:00:00",
                        "tododate" => "2018-07-05 15:00:00", // 8 days 9 hours
                        "actions" => [
                            "state" => "e3",
                            "tmail" => [],
                            "method" => "::Hello()"
                        ]
                    ]
                ),
                "refdate2" => "2018-06-20 03:00",
                "tasks2" => array(
                    [
                        "timerid" => "#TST_T002",
                        "title" => "Test TST_T002",
                        "referencedate" => "2018-06-22 06:00:00", // 2 days 3 hours
                        "tododate" => "2018-06-24 08:00:00", // 2 days 2 hours
                        "actions" => [
                            "state" => "",
                            "tmail" => ["#TST_M002_E2E1", "#TST_M002_E2E3"],
                            "method" => ""

                        ]
                    ],
                    [
                        "timerid" => "#TST_T002",
                        "title" => "Test TST_T002",
                        "referencedate" => "2018-06-22 06:00:00",
                        "tododate" => "2018-06-30 15:00:00", // 8 days 9 hours
                        "actions" => [
                            "state" => "e3",
                            "tmail" => [],
                            "method" => "::Hello()"
                        ]
                    ]
                )

            ],

            [
                "tid" => "TST_T002Bis",
                "refdate1" => "2018-10-05",
                "tasks1" => array(
                    [
                        "timerid" => "#TST_T002Bis",
                        "title" => "Test TST_T002Bis",
                        "referencedate" => "2018-10-15 00:00:00", // 10 days
                        "tododate" => "2018-10-15 00:00:00", // 0
                        "actions" => [
                            "state" => "",
                            "tmail" => ["#TST_M002_E2E1"],
                            "method" => ""

                        ]
                    ],
                    [
                        "timerid" => "#TST_T002Bis",
                        "title" => "Test TST_T002Bis",
                        "referencedate" => "2018-10-15 00:00:00", // 10 days
                        "tododate" => "2018-10-16 00:00:00", // 24 hours
                        "actions" => [
                            "state" => "",
                            "tmail" => ["#TST_M002_E2E1", "#TST_M002_E2E3"],
                            "method" => ""
                        ]
                    ],
                    [
                        "timerid" => "#TST_T002Bis",
                        "title" => "Test TST_T002Bis",
                        "referencedate" => "2018-10-15 00:00:00", // 10 days
                        "tododate" => "2018-11-15 00:00:00", // 1 month
                        "actions" => [
                            "state" => "e2",
                            "tmail" => ["#TST_M002_E2E3", "#TST_M002_E2E1"],
                            "method" => '::Hello("World", tst_desc)'
                        ]
                    ]
                ),
                "refdate2" => "2018-11-05",
                "tasks3" => array(
                    [
                        "timerid" => "#TST_T002Bis",
                        "title" => "Test TST_T002Bis",
                        "referencedate" => "2018-11-15 00:00:00", // 10 days
                        "tododate" => "2018-11-15 00:00:00", // 0
                        "actions" => [
                            "state" => "",
                            "tmail" => ["#TST_M002_E2E1"],
                            "method" => ""

                        ]
                    ],
                    [
                        "timerid" => "#TST_T002Bis",
                        "title" => "Test TST_T002Bis",
                        "referencedate" => "2018-11-15 00:00:00", // 10 days
                        "tododate" => "2018-11-16 00:00:00", // 24 hours
                        "actions" => [
                            "state" => "",
                            "tmail" => ["#TST_M002_E2E1", "#TST_M002_E2E3"],
                            "method" => ""
                        ]
                    ],
                    [
                        "timerid" => "#TST_T002Bis",
                        "title" => "Test TST_T002Bis",
                        "referencedate" => "2018-11-15 00:00:00", // 10 days
                        "tododate" => "2018-12-15 00:00:00", // 1 month
                        "actions" => [
                            "state" => "e2",
                            "tmail" => ["#TST_M002_E2E3", "#TST_M002_E2E1"],
                            "method" => '::Hello("World", tst_desc)'
                        ]
                    ]
                )
            ]
        ];
    }

    public function dataTimerAttach()
    {

        return [
            [
                "tid" => "TST_T002",
                "refdate" => "2018-06-25 03:00",
                "tasks" => array(
                    [
                        "timerid" => "#TST_T002",
                        "title" => "Test TST_T002",
                        "referencedate" => "2018-06-27 06:00:00", // 2 days 3 hours
                        "tododate" => "2018-06-29 08:00:00", // 2 days 2 hours
                        "actions" => [
                            "state" => "",
                            "tmail" => ["#TST_M002_E2E1", "#TST_M002_E2E3"],
                            "method" => ""

                        ]
                    ],
                    [
                        "timerid" => "#TST_T002",
                        "title" => "Test TST_T002",
                        "referencedate" => "2018-06-27 06:00:00",
                        "tododate" => "2018-07-05 15:00:00", // 8 days 9 hours
                        "actions" => [
                            "state" => "e3",
                            "tmail" => [],
                            "method" => "::Hello()"
                        ]
                    ]
                )

            ],

            [
                "tid" => "TST_T002Bis",
                "refdate" => "2018-10-05",
                "tasks" => array(
                    [
                        "timerid" => "#TST_T002Bis",
                        "title" => "Test TST_T002Bis",
                        "referencedate" => "2018-10-15 00:00:00", // 10 days
                        "tododate" => "2018-10-15 00:00:00", // 0
                        "actions" => [
                            "state" => "",
                            "tmail" => ["#TST_M002_E2E1"],
                            "method" => ""

                        ]
                    ],
                    [
                        "timerid" => "#TST_T002Bis",
                        "title" => "Test TST_T002Bis",
                        "referencedate" => "2018-10-15 00:00:00", // 10 days
                        "tododate" => "2018-10-16 00:00:00", // 24 hours
                        "actions" => [
                            "state" => "",
                            "tmail" => ["#TST_M002_E2E1", "#TST_M002_E2E3"],
                            "method" => ""
                        ]
                    ],
                    [
                        "timerid" => "#TST_T002Bis",
                        "title" => "Test TST_T002Bis",
                        "referencedate" => "2018-10-15 00:00:00", // 10 days
                        "tododate" => "2018-11-15 00:00:00", // 1 month
                        "actions" => [
                            "state" => "e2",
                            "tmail" => ["#TST_M002_E2E3", "#TST_M002_E2E1"],
                            "method" => '::Hello("World", tst_desc)'
                        ]
                    ]
                )
            ]
        ];
    }

    public function dataTimerData()
    {
        return [
            [
                "TST_T002",
                array(
                    'tm_title' => 'Test minute',
                    'tm_family' => '#TST_I002',
                    'tm_workflow' => '#TST_W002',
                    'tm_dyndate' => 'tst_datehour',
                    'tm_deltainterval' => '2 days 3 hours',
                    'tm_taskinterval' => '{"2 days 2 hours","8 days 9 hours"}',
                    'tm_tmail' => '{{#TST_M002_E2E1,#TST_M002_E2E3},{NULL,NULL}}',
                    'tm_state' => '{NULL,e3}',
                    'tm_method' => '{NULL,::Hello()}',
                )

            ],
            [
                "TST_T002Bis",
                array(
                    'tm_title' => 'Minute papillon',
                    'tm_family' => '#TST_I002',
                    'tm_workflow' => '#TST_W002',
                    'tm_dyndate' => 'tst_date',
                    'tm_deltainterval' => '10 days',
                    'tm_taskinterval' => '{"24 hours",0,"1 month"}',
                    'tm_tmail' => '{{#TST_M002_E2E1,#TST_M002_E2E3},{#TST_M002_E2E1,NULL},{#TST_M002_E2E3,#TST_M002_E2E1}}',
                    'tm_state' => '{NULL,NULL,e2}',
                    'tm_method' => '{NULL,NULL,"::Hello(\\"World\\", tst_desc)"}',
                )

            ]
        ];
    }

    //endregion
}
