<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

class SuiteDcpSecurity
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');

        $suite->addTestSuite('Dcp\Pu\TestRole');
        $suite->addTestSuite('Dcp\Pu\TestRoleMove');
        $suite->addTestSuite('Dcp\Pu\TestEditControl');
        // ...
        return $suite;
    }
}
