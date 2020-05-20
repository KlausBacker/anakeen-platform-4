<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

//require_once 'PU_testcase_dcp.php';

use Anakeen\Core\AccountManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Exception;

class TestSetParam extends TestCaseDcp
{
    /**
     * @dataProvider dataSetGlobParameter
     */
    public function testSetGlobParameter($name, $ns, $value)
    {
        \Anakeen\Core\ContextManager::setParameterValue($ns, $name, $value);

        $getValue = \Anakeen\Core\ContextManager::getParameterValue($ns, $name);

        $this->assertEquals($value, $getValue);
    }

    /**
     * @dataProvider dataSetUserParameter
     */
    public function testSetUserParameter($login, $name, $ns, $value)
    {
        \Anakeen\Core\ContextManager::setParameterValue($ns, $name, $value);
        $u = AccountManager::getAccount($login);

        ContextParameterManager::setUserValue($ns, $name, $value, $u->id);

        $getValue = ContextParameterManager::getUserValue($ns, $name, $u->id);

        $this->assertEquals($value, $getValue);
    }


    /**
     * @dataProvider dataErrorSetGlobParameter
     */
    public function testErrorSetGlobParameter($name, $ns, $value)
    {
        try {
            \Anakeen\Core\ContextManager::setParameterValue($ns, $name, $value);
            $this->assertTrue(false, "A error must occurs");
        } catch (Exception $e) {
            $this->assertEquals("CORE0102", $e->getDcpCode());
        }
    }


    /**
     * @dataProvider dataErrorSetUserParameter
     */
    public function testErrorSetUserParameter($login, $name, $ns, $value)
    {
        $u = AccountManager::getAccount($login);
        try {
            ContextParameterManager::setUserValue($ns, $name, $value, $u->id);
            $this->assertTrue(false, "A error must occurs");
        } catch (Exception $e) {
            $this->assertEquals("CORE0103", $e->getDcpCode());
        }
    }

    public function dataSetGlobParameter()
    {
        return array(
            [
                'name' => 'CORE_CLIENT',
                'ns' => 'Core',
                'value' => 'Yeah'
            ],
            [
                'name' => 'CORE_SESSIONMAXAGE',
                'ns' => 'Core',
                'value' => '1 day'
            ]
        );
    }


    public function dataSetUserParameter()
    {
        return array(
            [
                'login' => 'anonymous',
                'name' => 'CORE_LANG',
                'ns' => 'Core',
                'value' => 'en_US'
            ]
        );
    }

    public function dataErrorSetUserParameter()
    {
        return array(
            [
                'login' => 'anonymous',
                'name' => 'Bof',
                'ns' => 'Bof',
                'value' => 'en_US'
            ],

            [
                'login' => 'anonymous',
                'name' => 'CORE_SESSIONMAXAGE',
                'ns' => 'Core',
                'value' => '1 day'
            ]
        );
    }


    public function dataErrorSetGlobParameter()
    {
        return array(
            [
                'name' => 'Bof',
                'ns' => 'Core',
                'value' => 'Yeah'
            ],
            [
                'name' => 'CORE_SESSIONMAXAGE',
                'ns' => 'Bof',
                'value' => '1 day'
            ]
        );
    }
}
