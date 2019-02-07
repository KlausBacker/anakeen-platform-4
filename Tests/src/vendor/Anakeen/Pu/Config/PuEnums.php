<?php

namespace Anakeen\Pu\Config;

use Anakeen\Core\EnumManager;

class PuEnums extends TestCaseConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_005.enum.xml");
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_005bis.enum.xml");
    }

    /**
     * Test Field definition import
     *
     * @dataProvider dataEnum
     * @param       $enumSetName
     * @param array $expectedEnums
     * @throws \Anakeen\Database\Exception
     */
    public function testEnum($enumSetName, $expectedCount, array $expectedEnums)
    {
        $enumItems = EnumManager::getEnums($enumSetName);
        $this->assertEquals($expectedCount, count($enumItems), "Not good count enum items count");
        foreach ($expectedEnums as $enumKey => $enumInfo) {
            $this->assertTrue(isset($enumItems[$enumKey]), "Enum $enumKey not found");
            foreach ($enumInfo as $key => $value) {
                $this->assertEquals($value, $enumItems[$enumKey][$key], "Wrong $key for Enum $enumKey ");
            }
        }
    }


    //region Test Data providers
    public function dataEnum()
    {
        return [
            [
                "TST_005-colors",
                6,
                [
                    "red" => ["label" => "Rouge"],
                    "blue" => ["label" => "Bleu"],
                    "lightblue" => ["longLabel" => "Bleu/Bleu ciel", "path" => "blue.lightblue"]
                ]
            ],
            [
                "TST_005-notes",
                3,
                [
                    "A" => ["label" => "La"],
                    "C" => ["label" => "Do"],
                    "D" => ["label" => "Ré"],
                ]
            ],
            [
                "TST_005-letters",
                26,
                [
                    "A" => ["label" => "Letter a"],
                    "Z" => ["label" => "Letter z"],
                ]
            ],
            [
                "TST_005-country",
                18,
                [
                    "europa" => ["label" => "Europe"],
                    "Paris" => [
                        "label" => "Paris",
                        "path" => "europa.france.Paris",
                        "longLabel" => "Europe/France/Paris"
                    ],
                    "Besançon" => ["label" => "Besançon"],
                    "morroco" => ["label" => "Maroc", "path" => "africa.morroco", "longLabel" => "Afrique/Maroc"],
                    "Casablanca" => ["label" => "Casablanca"],
                ]
            ],
        ];
    }


    //endregion
}
