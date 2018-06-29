<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

//require_once 'PU_testcase_dcp.php';

class TestGetParam extends TestCaseDcp
{
    /**
     * @dataProvider dataGetCoreParamNonExisting
     */
    public function testGetCoreParamNonExisting($data)
    {
        $value = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, $data['name'], $data['def']);
        
        $sameType = (gettype($value) == gettype($data['expected']));
        $sameValue = ($value == $data['expected']);
        
        $this->assertTrue($sameType, sprintf("Result type mismatch: found type '%s' while expecting type '%s'.", gettype($value) , gettype($data['expected'])));
        $this->assertTrue($sameValue, sprintf("Unexpected result: found '%s' while expecting '%s'.", $value, $data['expected']));
    }
    /**
     * @dataProvider dataGetParamNonExisting
     */
    public function testGetParamNonExisting($data)
    {
        $value = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, $data['name'], $data['def']);
        
        $sameType = (gettype($value) == gettype($data['expected']));
        $sameValue = ($value == $data['expected']);
        
        $this->assertTrue($sameType, sprintf("Result type mismatch: found type '%s' while expecting type '%s'.", gettype($value) , gettype($data['expected'])));
        $this->assertTrue($sameValue, sprintf("Unexpected result: found '%s' while expecting '%s'.", $value, $data['expected']));
    }
    /**
     * @dataProvider dataGetCoreParamIsSet
     */
    public function testGetCoreParamIsSet($data)
    {
        $value = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, $data['name'], null);
        
        $this->assertTrue(($value !== null) , "Returned value is not set.");
    }
    /**
     * @dataProvider dataGetCoreParamIsSet
     */
    public function testGetParamIsSet($data)
    {
        $value = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, $data['name'], null);
        
        $this->assertTrue(($value !== null) , "Returned value is not set.");
    }

    
    public function dataGetParamDef()
    {
        return array(
            array(
                'name' => 'CORE_NON_EXISTING_PARAM',
                'app' => 'CORE',
                'expected' => ''
            ) ,

            array(
                'name' => 'CORE_CLIENT',
                'app' => 'CORE',
                'expected' => array(
                    "name" => "CORE_CLIENT",
                    "isglob" => "Y"
                )
            ) ,
            array(
                'name' => 'CORE_CLIENT',
                'app' => '',
                'expected' => array(
                    "name" => "CORE_CLIENT",
                    "isglob" => "Y"
                )
            ) ,
            array(
                'name' => 'CORE_CLIENT',
                'app' => 'FDL',
                'expected' => array(
                    "name" => "CORE_CLIENT",
                    "isglob" => "Y"
                )
            ) ,
            array(
                'name' => 'CORE_CLIENT',
                'app' => 'FDL',
                'expected' => array(
                    "name" => "CORE_CLIENT",
                    "isglob" => "Y"
                )
            )
        );
    }
    
    public function dataGetCoreParamNonExisting()
    {
        return array(
            array(
                array(
                    'name' => 'CORE_NON_EXISTING_PARAM',
                    'def' => 'DOES_NOT_EXISTS',
                    'expected' => 'DOES_NOT_EXISTS'
                )
            )
        );
    }
    
    public function dataGetParamNonExisting()
    {
        return array(
            array(
                array(
                    'name' => 'CORE_NON_EXISTING_PARAM',
                    'def' => 'DOES_NOT_EXISTS',
                    'expected' => 'DOES_NOT_EXISTS'
                )
            )
        );
    }
    
    public function dataGetCoreParamIsSet()
    {
        return array(
            array(
                array(
                    'name' => 'CORE_CLIENT'
                    // CORE 'G'
                    
                ) ,
                array(
                    'name' => 'CORE_DB'
                    // CORE 'A'
                    
                )
            )
        );
    }
    
    public function dataGetParamIsSet()
    {
        return array(
            array(
                array(
                    'name' => 'CORE_CLIENT'
                    // CORE 'G'
                    
                ) ,
                array(
                    'name' => 'CORE_DB'
                    // CORE 'A'
                    
                )
            )
        );
    }
}
?>