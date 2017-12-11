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

require_once 'WHAT/autoload.php';
class SuiteWorkflow
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        
        $suite->addTestSuite('Dcp\Pu\TestFormatCollection');

        $suite->addTestSuite('Dcp\Pu\TestExtendProfil');
        $suite->addTestSuite('Dcp\Pu\TestImportAccess');
        $suite->addTestSuite('Dcp\Pu\TestImportFamilyProperty');
        $suite->addTestSuite('Dcp\Pu\TestImportWorkflow');
        $suite->addTestSuite('Dcp\Pu\TestWorflowTransition');
        $suite->addTestSuite('Dcp\Pu\TestImportDocumentsExtra');
        $suite->addTestSuite('Dcp\Pu\TestDocControl');

        // ...
        return $suite;
    }
}
