<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

//require_once 'PU_testcase_dcp.php';

use Anakeen\Core\AccountManager;
use Anakeen\Core\Internal\Param;
use Anakeen\Exception;

class TestGetParam extends TestCaseDcp
{
    /**
     * @dataProvider dataGetCoreParamNonExisting
     */
    public function testGetCoreParamNonExisting($data)
    {
        $value = \Anakeen\Core\ContextManager::getParameterValue(
            \Anakeen\Core\Settings::NsSde,
            $data['name'],
            $data['def']
        );

        $sameType = (gettype($value) == gettype($data['expected']));
        $sameValue = ($value == $data['expected']);

        $this->assertTrue(
            $sameType,
            sprintf(
                "Result type mismatch: found type '%s' while expecting type '%s'.",
                gettype($value),
                gettype($data['expected'])
            )
        );
        $this->assertTrue(
            $sameValue,
            sprintf("Unexpected result: found '%s' while expecting '%s'.", $value, $data['expected'])
        );
    }

    /**
     * @dataProvider dataGetParamNonExisting
     */
    public function testGetParamNonExisting($data)
    {
        $value = \Anakeen\Core\ContextManager::getParameterValue(
            \Anakeen\Core\Settings::NsSde,
            $data['name'],
            $data['def']
        );

        $sameType = (gettype($value) == gettype($data['expected']));
        $sameValue = ($value == $data['expected']);

        $this->assertTrue(
            $sameType,
            sprintf(
                "Result type mismatch: found type '%s' while expecting type '%s'.",
                gettype($value),
                gettype($data['expected'])
            )
        );
        $this->assertTrue(
            $sameValue,
            sprintf("Unexpected result: found '%s' while expecting '%s'.", $value, $data['expected'])
        );
    }

    /**
     * @dataProvider dataGetCoreParamIsSet
     */
    public function testGetCoreParamIsSet($data)
    {
        $value = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, $data['name'], null);

        $this->assertTrue(($value !== null), "Returned value is not set.");
    }

    /**
     * @dataProvider dataGetCoreParamIsSet
     */
    public function testGetParamIsSet($data)
    {
        $value = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, $data['name'], null);

        $this->assertTrue(($value !== null), "Returned value is not set.");
    }


    /**
     * @dataProvider dataGetUserParam
     */
    public function testGetUserParam($data)
    {
        $u = AccountManager::getAccount($data['login']);
        \Anakeen\Core\Internal\ContextParameterManager::setUserValue(
            \Anakeen\Core\Settings::NsSde,
            $data['name'],
            $data["expected"],
            $u->id
        );

        $value = \Anakeen\Core\Internal\ContextParameterManager::getUserValue(
            \Anakeen\Core\Settings::NsSde,
            $data['name'],
            $u->id,
            $data["def"]
        );

        $this->assertEquals($value, $data["expected"], "Not the value for {$u->login}.");

        \Anakeen\Core\Internal\ContextParameterManager::setUserValue(
            \Anakeen\Core\Settings::NsSde,
            $data['name'],
            null,
            $u->id
        );
        $value = \Anakeen\Core\Internal\ContextParameterManager::getUserValue(
            \Anakeen\Core\Settings::NsSde,
            $data['name'],
            $u->id,
            $data["def"]
        );
        $p = new Param("", [\Anakeen\Core\Settings::NsSde . "::" . $data['name'], Param::PARAM_GLB]);

        $this->assertEquals($value, $p->val, "Not the value for {$u->login}.");
    }


    /**
     * @dataProvider dataErrorGetUserParam
     */
    public function testErrorGetUserParam($data)
    {
        $u = AccountManager::getAccount($data['login']);
        try {
            \Anakeen\Core\Internal\ContextParameterManager::getUserValue(
                \Anakeen\Core\Settings::NsSde,
                $data['name'],
                $u->id,
                $data["def"]
            );
            $this->assertTrue(false, "An error must occurs");
        } catch (Exception $e) {
            $this->assertEquals("CORE0104", $e->getDcpCode());
        }
    }

    public function dataGetUserParam()
    {
        return array(
            array(
                array(
                    'login' => 'anonymous',
                    'name' => 'CORE_LANG',
                    'def' => 'DOES_NOT_EXISTS',
                    'expected' => 'es_ES'
                )
            )
        );
    }


    public function dataErrorGetUserParam()
    {
        return array(
            array(
                array(
                    'login' => 'anonymous',
                    'name' => 'SMTP_LOGIN',
                    'def' => 'DOES_NOT_EXISTS',
                    'expected' => 'DOES_NOT_EXISTS'
                )
            )
        );
    }

    public function dataGetParamDef()
    {
        return array(
            array(
                'name' => 'CORE_NON_EXISTING_PARAM',
                'app' => 'CORE',
                'expected' => ''
            ),

            array(
                'name' => 'CORE_CLIENT',
                'app' => 'CORE',
                'expected' => array(
                    "name" => "CORE_CLIENT",
                    "isglob" => "Y"
                )
            ),
            array(
                'name' => 'CORE_CLIENT',
                'app' => '',
                'expected' => array(
                    "name" => "CORE_CLIENT",
                    "isglob" => "Y"
                )
            ),
            array(
                'name' => 'CORE_CLIENT',
                'app' => 'FDL',
                'expected' => array(
                    "name" => "CORE_CLIENT",
                    "isglob" => "Y"
                )
            ),
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

                ),
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

                ),
                array(
                    'name' => 'CORE_DB'
                    // CORE 'A'

                )
            )
        );
    }
}
