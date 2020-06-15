<?php

namespace Dcp\Pu;

use Anakeen\Pu\SmartStructures\WTestControlMX;
use Anakeen\SmartElementManager;

class TestWorflowMXControl extends TestCaseDcpCommonFamily
{
    /**
     * import TST_DEFAULTFAMILY1 family
     *
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_impworkflowfamilyControlMx.csv";
    }

    /**
     * @dataProvider dataTransitionNotUnderControl
     *
     * @param $login
     * @param $docName
     * @param $nextState
     * @param $expectedValues
     */
    public function testTransitionNotUnderControl($login, $docName, $nextState, $expectedValues)
    {
        $this->sudo($login);
        $doc = SmartElementManager::getDocument($docName);

        $this->assertTrue($doc && $doc->isAlive(), "cannot find document $docName");

        $err=$doc->setState($nextState);
        $this->assertEmpty($err, "Transition error :$err");

            $this->exitSudo();
        foreach ($expectedValues as $attrid => $value) {
            $this->assertEquals($value, $doc->getRawValue($attrid));
        }
    }



    public function dataTransitionNotUnderControl()
    {
        return array(
            array(
                "admin",
                'TST_MX1',
                WTestControlMX::SB,
                [
                    "tst_title"=>"Hello-M0--M1--M2--M3-",
                    "tst_number"=>12+1+2+3+4,
                    "tst_date"=>"2000-12-23"
                ]
            ),
            array(
                "user_tstmx",
                'TST_MX1',
                WTestControlMX::SB,
                [
                    "tst_title"=>"Hello-M0--M1--M2--M3-",
                    "tst_number"=>12+1+2+3+4,
                    "tst_date"=>"2000-12-23"
                ]
            ),

            array(
                "user_tstmx",
                'TST_MX2',
                WTestControlMX::SB,
                [
                    "tst_title"=>"Bonjour-M0--M1--M2--M3-",
                    "tst_number"=>65+1+2+3+4,
                    "tst_date"=>"1999-01-26"
                ]
            )
        );
    }
}
