<?php

namespace Dcp\Pu;

/**
 * @author Anakeen
 * @package Dcp\Pu
 */

use Dcp\Core\ContextManager;

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
include_once "FDL/Class.Doc.php";

class FrameworkDcp extends \PHPUnit_Framework_TestSuite
{
    protected function setUp()
    {
        $action = ContextManager::getCurrentAction();

        if (!$action) {
            $u = new \Account();
            $u->setLoginName("admin");
            \Dcp\Core\ContextManager::initContext($u);
        }
    }

    protected function tearDown()
    {

    }


}

