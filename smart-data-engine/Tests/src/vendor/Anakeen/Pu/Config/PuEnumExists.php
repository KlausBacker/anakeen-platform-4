<?php

namespace Anakeen\Pu\Config;

class PuEnumExists extends \Dcp\Pu\TestCaseDcpCommonFamily
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
                __DIR__ . "/Inputs/EnumExists/tst_enum_exists_good.xml"
            ]
        ];
    }

    public function dataErrorFile()
    {
        return [
            [
                __DIR__ . "/Inputs/EnumExists/tst_enum_exists_error.xml", "ATTR1274"
            ],
        ];
    }
}
