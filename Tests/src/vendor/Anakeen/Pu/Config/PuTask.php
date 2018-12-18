<?php

namespace Anakeen\Pu\Config;

use Anakeen\Core\SEManager;
use SmartStructure\Fields\Task as TaskFields;

class PuTask extends TestCaseConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_001.task.xml");
    }

    /**
     * Test Mask definition import
     *
     * @dataProvider dataMaskImport
     *
     * @param string $taskName
     * @param array  $expectedFields
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function testMaskImport($taskName, array $expectedFields)
    {
        $task = SEManager::getDocument($taskName);
        $this->assertNotEmpty($task, "Task $taskName not found");

        foreach ($expectedFields as $key => $value) {
            switch ($key) {
                case "title":
                    $this->assertEquals($value, $task->getTitle());
                    break;
                case TaskFields::task_iduser:
                    $this->assertEquals($value, SEManager::getRawValue($task->getRawValue($key), "us_login"));
                    break;
                default:
                    if (is_array($value)) {
                        $this->assertEquals($value, $task->getMultipleRawValues($key), sprintf("\"$key\"` not match"));
                    } else {
                        $this->assertEquals($value, $task->getRawValue($key), sprintf("\"$key\"` not match"));
                    }
            }
        }
    }

    public function dataMaskImport()
    {
        return [
            [
                "TST_TASK0001",
                [
                    "title" => "Ma lourde tâche",
                    TaskFields::task_desc => "Quelque chose à faire...",
                    TaskFields::task_crontab => "4,30 6 * * 1-5",
                    TaskFields::task_status => "active",
                    TaskFields::task_iduser => "admin",
                    TaskFields::task_route_method => "POST",
                    TaskFields::task_route_name => "theroute",
                    TaskFields::task_route_ns => "Test",
                    TaskFields::task_arg_name => ["c","x"],
                    TaskFields::task_arg_value => ["Hello", "R2D2"],
                    TaskFields::task_queryfield_name => ["y"],
                    TaskFields::task_queryfield_value => ["World"],
                ]
            ]
        ];
    }
}
