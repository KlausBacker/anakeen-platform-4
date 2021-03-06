<?php

namespace Dcp\Pu;

/**
 * @author  Anakeen
 * @package Dcp\Pu
 */

use Anakeen\Core\ContextManager;

class FrameworkDcp extends \PHPUnit\Framework\TestSuite
{
    protected function setUp(): void
    {
        if (!ContextManager::isAuthenticated()) {
            $u = new \Anakeen\Core\Account();
            $u->setLoginName("admin");
            \Anakeen\Core\ContextManager::initContext($u);
        }
    }

    protected function tearDown(): void
    {
    }
}
