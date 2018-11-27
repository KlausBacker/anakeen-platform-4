<?php

namespace Anakeen\Pu\Config;

use Anakeen\Core\SEManager;


class PuStructBasic extends TestCaseConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_001.struct.xml");
    }

    /**
     * Test Field definition import
     *
     * @dataProvider dataFieldStructure
     *
     *
     */
    public function testFieldStructure($structureName, $expectedFields)
    {
        $structure = SEManager::getFamily($structureName);
        $this->assertNotEmpty($structure, "Structure $structureName not found");

        foreach ($expectedFields as $id => $expectedField) {
            $oa = $structure->getAttribute($id);
            $this->assertNotEmpty($oa, "Attribute $id not found");
            $this->assertEquals($expectedField["type"], $oa->type);
        }

    }

    public function dataFieldStructure()
    {
        return [
            [
                "TST_001",
                [
                    "tst_f_title" => [
                        "type" => "frame"
                    ]
                ]
            ]
        ];
    }


}
