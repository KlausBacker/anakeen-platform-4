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

//require_once 'WHAT/autoload.php';
class SuiteDcpAttribute
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        $suite->addTestSuite('Dcp\Pu\TestTypedValue');
        $suite->addTestSuite('Dcp\Pu\TestHtmlValue');
        $suite->addTestSuite('Dcp\Pu\TestAddArrayRow');
        $suite->addTestSuite('Dcp\Pu\TestGetEnum');
        $suite->addTestSuite('Dcp\Pu\TestDocEnum');
        $suite->addTestSuite('Dcp\Pu\TestAttributeOrder');
        $suite->addTestSuite('Dcp\Pu\TestAttributeValue');
        $suite->addTestSuite('Dcp\Pu\TestAttributeDefault');
        $suite->addTestSuite('Dcp\Pu\TestAttributeCompute');
        $suite->addTestSuite('Dcp\Pu\TestAttributeDate');
        $suite->addTestSuite('Dcp\Pu\TestAttributeSlashes');
        $suite->addTestSuite('Dcp\Pu\TestGetSearchMethods');
        $suite->addTestSuite('Dcp\Pu\TestGetSortAttributes');
        $suite->addTestSuite('Dcp\Pu\TestGetSortProperties');
        $suite->addTestSuite('Dcp\Pu\TestLFamily');
        $suite->addTestSuite('Dcp\Pu\TestGetDocTitle');
        $suite->addTestSuite('Dcp\Pu\TestGetDocAnchor');
        $suite->addTestSuite('Dcp\Pu\TestGetTextualValue');
        $suite->addTestSuite('Dcp\Pu\TestAttributeDoctitle');
        // ...
        return $suite;
    }
}
