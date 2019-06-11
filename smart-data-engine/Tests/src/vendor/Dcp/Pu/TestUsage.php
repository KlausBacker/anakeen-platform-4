<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

/**
 * @author  Anakeen
 * @package Dcp\Pu
 */

use Anakeen\Script\ApiUsage;
use Anakeen\Script\ShellManager;

//require_once 'PU_testcase_dcp.php';

class TestUsage extends TestCaseDcp
{


    protected function tearDown()
    {
        parent::tearDown();
        ShellManager::recordArgs([]);
    }
    /**
     *
     * @dataProvider dataTextUsage
     *
     * @param string $text
     */
    public function testTextApiUsage($text)
    {
        $u = new \Anakeen\Script\ApiUsage();
        $u->setDefinitionText($text);

        $this->assertContains($text, $u->getUsage());
    }

    /**
     *
     * @dataProvider dataNeedUsage
     *
     * @param $argNeeded
     * @param $def
     */
    public function testNeededApiUsage($argNeeded, $def)
    {
        $usage = '';
        try {
            $u = new \Anakeen\Script\ApiUsage();
            $u->addRequiredParameter($argNeeded, $def);
            $u->verify();
        } catch (\Exception $e) {
            $usage = $e->getMessage();
        }
        $this->assertContains($argNeeded, $usage);
        $this->assertContains($def, $usage);
    }

    /**
     *
     * @dataProvider dataNeedUsage
     *
     * @param $argNeeded
     * @param $def
     */
    public function testNeededApiUsageForceException($argNeeded, $def)
    {
        $usage = '';
        $error = '';
        try {
            $u = new \Anakeen\Script\ApiUsage();
            $u->addRequiredParameter($argNeeded, $def);
            $u->verify(true);
        } catch (\Anakeen\Script\UsageException $e) {
            $error = $e->getMessage();
            $usage = $e->getUsage();
        }
        $this->assertContains($argNeeded, $error);
        $this->assertNotContains($def, $error);
        $this->assertContains($argNeeded, $usage);
        $this->assertContains($def, $usage);
    }

    public static function usageCallback($values, $argName, $apiUsage)
    {
        if ($values === null) {
            return sprintf("This is the usage for argument %s", $argName);
        }
        if (!is_scalar($values)) {
            return sprintf("Error in usageCallback for argument %s: type of value %s must be string", $argName, gettype($values));
        }
        return "";
    }

    /**
     * @dataProvider dataCallbackUsage
     *
     * @param $callback
     */
    public function testGoodCallbackUsage($callback)
    {
        $usage = '';
        try {
            ShellManager::recordArgs(["--needed=needed"]);
            $u = new \Anakeen\Script\ApiUsage();
            $u->addRequiredParameter("needed", "A needed argument", $callback);
            $u->addOptionalParameter("optional", "An optional argument", $callback);
            $u->addHiddenParameter("hidden", "An hidden argument");
            $u->verify(true);
        } catch (\Exception $e) {
            $usage = $e->getMessage();
        }
        $this->assertEmpty($usage);
    }

    /**
     * @dataProvider dataCallbackUsage
     *
     * @param $callback
     */
    public function testBadCallbackNeededUsage($callback)
    {
        $error = '';
        $myvar = "myvariable";
        try {
            ShellManager::recordArgs([sprintf("--%s[]=%s", $myvar, $myvar)]);
            $u = new \Anakeen\Script\ApiUsage();
            $u->addRequiredParameter($myvar, "A needed argument", $callback);
            $u->verify(true);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        $this->assertContains("usageCallback", $error);
        $this->assertContains($myvar, $error);
    }

    /**
     * @dataProvider dataCallbackUsage
     *
     * @param $callback
     */
    public function testBadCallbackOptinalUsage($callback)
    {
        $error = '';
        $myvar = "myvariable";
        try {
            ShellManager::recordArgs([sprintf("--%s[]=%s", $myvar, $myvar)]);
            $u = new \Anakeen\Script\ApiUsage();
            $u->addOptionalParameter($myvar, "An optional argument", $callback);
            $u->verify(true);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        $this->assertContains("usageCallback", $error);
        $this->assertContains($myvar, $error);
    }

    /**
     * @dataProvider dataCallbackUsage
     *
     * @param $callback
     */
    public function testCallbackUsage($callback)
    {
        $usage = '';
        try {
            ShellManager::recordArgs(["--help"]);
            $u = new \Anakeen\Script\ApiUsage();
            $u->addRequiredParameter("needed", "A needed argument", $callback);
            $u->addOptionalParameter("optional", "An optional argument", $callback);
            $u->addHiddenParameter("hidden", "An hidden argument");
            $u->verify();
        } catch (\Exception $e) {
            $usage = $e->getMessage();
        }
        $this->assertContains("CORE0003", $usage, sprintf("usage found is %s", $usage));
        $this->assertNotContains("CORE0002", $usage, sprintf("usage found is %s", $usage));
    }

    public function dataCallbackUsage()
    {
        return array(
            array(
                "\Dcp\Pu\TestUsage::usageCallback",
                "\Dcp\Pu\simpleFunctionUsageCallback",
                array(
                    $this,
                    "usageCallback"
                ),
                function ($values, $argName, $apiUsage) {
                    return TestUsage::usageCallback($values, $argName, $apiUsage);
                }
            )
        );
    }

    public function dataTextUsage()
    {
        return array(
            array(
                "hello world"
            )
        );
    }

    public function dataNeedUsage()
    {
        return array(
            array(
                "needAbsolut",
                "necessary"
            )
        );
    }
}

function simpleFunctionUsageCallback($values, $argName, ApiUsage $apiUsage)
{
    return TestUsage::usageCallback($values, $argName, $apiUsage);
}
