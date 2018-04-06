<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */

//require_once 'PU_testcase_dcp_commonfamily.php';

class TestAutoloader extends TestCaseDcpCommonFamily
{
    /**
     * import TST_DEFAULTFAMILY1 family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_goodfamily3.ods";
    }
    /**
     * @dataProvider dataDAutoloadFamily
     */
    public function testAutoloadFamily($className)
    {
        
        $this->assertTrue(class_exists($className) , sprintf("class not found %s", $className));
    }
    
    public function dataDAutoloadFamily()
    {
        return array(
            array(
                '\\SmartStructure\\Tst_goodfamal1'
            ) ,
            array(
                "\\SmartStructure\\Tst_goodfamal2"
            ) ,
            array(
                \SmartStructure\Base::class
            ) ,
            array(
                \SmartStructure\Iuser::class
            ) ,
            array(
                \SmartStructure\Dir::class
            )
        );
    }
}