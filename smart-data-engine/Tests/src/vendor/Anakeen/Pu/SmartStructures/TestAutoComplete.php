<?php

namespace Anakeen\Pu\SmartStructures;

class TestAutoComplete extends \Dcp\Pu\TestCaseDcpCommonFamily
{
    // $expectedAutoComplete

    /**
     * @dataProvider dataGoodFile
     * @param $filePath
     */
    public function testGoodFile($filePath)
    {
        try {
            self::importConfiguration($filePath);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertEmpty($e->getMessage(), "This test should not produce an error: " . $e->getMessage());
        }
    }

    /**
     * @dataProvider dataErrorFile
     * @param $filePath
     * @param $expectedErrorCode
     */
    public function testErrorFile($filePath, $expectedErrorCode)
    {
        try {
            self::importConfiguration($filePath);
            $this->assertEmpty("This test have an error, but not passed throw any error");
        } catch (\Anakeen\Exception $error) {
            if (strpos($error->getMessage(), $expectedErrorCode)) {
                $this->assertNotEmpty($error->getMessage(), "This test is good, we have an error");
            } else {
                $this->assertNotEmpty($error->getMessage(), "We found an error with invalid error Code");
            }
        } catch (\Exception $error) {
            $this->assertNotEmpty($error->getMessage(), "In this test we don't get an Anakeen Exception");
        }
    }

    public function dataGoodFile()
    {
        return [
            [
                __DIR__ . "/Inputs/GoodTest/Test0001AutoCompleteGood.xml"
            ],
            [
                __DIR__ . "/Inputs/GoodTest/Test0002AutoCompleteGood.xml"
            ],
        ];
    }

    public function dataErrorFile()
    {
        return [
            [
                __DIR__ . "/Inputs/ErrorTest/Test0001AutoCompleteError.xml", "ATTR1802"
            ],
            [
                __DIR__ . "/Inputs/ErrorTest/Test0002AutoCompleteError.xml", "UI0402"
            ],
            [
                __DIR__ . "/Inputs/ErrorTest/Test0003AutoCompleteError.xml", "ATTR1801"
            ],
        ];
    }
}
