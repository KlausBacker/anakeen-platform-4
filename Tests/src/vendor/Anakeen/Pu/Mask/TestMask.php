<?php

namespace Anakeen\Pu\Mask;

use Anakeen\Core\SEManager;
use Anakeen\Ui\MaskManager;
use Dcp\Pu\TestCaseDcpCommonFamily;

class TestMask extends TestCaseDcpCommonFamily
{
    /**
     * import some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return __DIR__."/PU_data_dcp_maskfamily.csv";
    }

    /**
     * apply mask
     * @dataProvider dataGoodMask
     * @param $docid
     * @param $mid
     */
    public function testUisetMask($docid, $mid, array $expectedVisibilities)
    {
        $doc = SEManager::getDocument($docid, true);

        if ($doc->isAlive()) {
            $maskMgt = new MaskManager($doc);
            $maskMgt->setUiMask($mid);

            foreach ($expectedVisibilities as $attrid => $expectVis) {
                $mvis = $maskMgt->getVisibility($attrid);
                $this->assertEquals($expectVis, $mvis, sprintf("Attribute $attrid"));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.'), $docid));
        }
    }

    /**
     * apply mask (detect errors)
     * @dataProvider dataBadMask
     * @param       $docid
     * @param       $mid
     * @param array $expectedErrors
     */
    public function testUisetMaskError($docid, $mid, array $expectedErrors)
    {

        $doc = SEManager::getDocument($docid, true);

        if ($doc && $doc->isAlive()) {
            $err = "";
            $maskMgt = new MaskManager($doc);
            try {
                $maskMgt->setUiMask($mid);
            } catch (\Exception $e) {
                $err = $e->getMessage();
            }
            $this->assertNotEmpty($err, sprintf("mask apply need error"));
            foreach ($expectedErrors as $error) {
                $this->assertContains($error, $err, sprintf("mask apply not correct error %s", $err));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.'), $docid));
        }
    }

    /**
     * @param       $file
     * @param array $expectedErrors
     * @dataProvider dataBadImportMask
     */
    public function testBadImportMask($file, array $expectedErrors)
    {
        try {
            self::importDocument($file);
            $this->assertTrue(false, "Mask import errors must not be empty");
        } catch (\Dcp\Exception $e) {
            foreach ($expectedErrors as $error) {
                $this->assertContains($error, $e->getMessage(), sprintf("mask apply not correct error %s", $e->getMessage()));
            }
        }
    }

    public function dataBadImportMask()
    {
        return array(
            [
                "PU_data_dcp_badMask.ods",
                [
                    "MSK0001",
                    "TST_BADMASK1",
                    "MSK0002",
                    "TST_BADMASK2",
                    "MSK0003",
                    "tst_titlex",
                    "tst_numberx",
                    "tst_p2"
                ]
            ]
        );
    }

    public function dataGoodMask()
    {
        return array(
            array(
                'TST_DOCBASE1',
                'TST_GOODMASK1',
                array(
                    "tst_title" => "R",
                    "tst_number" => "W",
                    "tst_date" => "W",
                    "tst_coltext" => "W",
                    "tst_coldate" => "W",
                    "tst_text" => "W"
                )
            ),
            array(
                'TST_DOCBASE1',
                'TST_GOODMASK2',
                array(
                    "tst_title" => "H",
                    "tst_number" => "H",
                    "tst_date" => "H",
                    "tst_coltext" => "H",
                    "tst_coldate" => "H",
                    "tst_text" => "W"
                )
            ),
            array(
                'TST_DOCBASE1',
                'TST_GOODMASK3',
                array(
                    "tst_title" => "H",
                    "tst_number" => "H",
                    "tst_date" => "H",
                    "tst_coltext" => "H",
                    "tst_coldate" => "H",
                    "tst_text" => "H"
                )
            ),
            array(
                'TST_DOCBASE1',
                'TST_GOODMASK4',
                array(
                    "tst_title" => "R",
                    "tst_number" => "R",
                    "tst_date" => "R",
                    "tst_coltext" => "W",
                    "tst_coldate" => "W",
                    "tst_text" => "H"
                )
            )
        );
    }

    public function dataBadMask()
    {
        return array(
            array(
                'TST_DOCBASE1',
                '878',
                array(
                    'DOC1000',
                    '878'
                )
            ),
            array(
                'TST_DOCBASE1',
                'TST_UNKNOW',
                array(
                    'DOC1004',
                    'TST_UNKNOW'
                )
            ),
            array(
                'TST_DOCBASE1',
                'TST_MASK2',
                array(
                    'DOC1002',
                    'TST_MASK2',
                    'IGROUP'
                )
            ),
            array(
                'TST_DOCBASE1',
                'TST_DOCBASE1',
                array(
                    'DOC1001',
                    'TST_DOCBASE1'
                )
            )
        );
    }
}
