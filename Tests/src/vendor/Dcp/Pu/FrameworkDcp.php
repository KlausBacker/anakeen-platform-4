<?php

namespace Dcp\Pu;

/**
 * @author  Anakeen
 * @package Dcp\Pu
 */

use Anakeen\Core\ContextManager;

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);

class FrameworkDcp extends \PHPUnit\Framework\TestSuite
{
    protected function setUp(): void
    {
        $user = ContextManager::getCurrentUser();

        if (!$user) {
            $u = new \Anakeen\Core\Account();
            $u->setLoginName("admin");
            \Anakeen\Core\ContextManager::initContext($u);
        }
    }

    protected function tearDown(): void
    {
    }
}
