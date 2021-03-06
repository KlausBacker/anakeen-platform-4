<?php

namespace Anakeen\Pu\Config;

class PuModAttr extends \Dcp\Pu\TestCaseDcpCommonFamily
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
        } catch (\Exception $error) {
            $this->assertNotEmpty($error->getMessage(), "In this test we don't get an Anakeen Exception");
        }
    }

    public function dataGoodFile()
    {
        return [
            [
                __DIR__ . "/Inputs/tst_overrride_good.xml"
            ],
        ];
    }

    public function dataErrorFile()
    {
        return [
            [
                __DIR__ . "/Inputs/tst_overrride_error.xml", "ATTR0215"
            ]
        ];
    }
}
