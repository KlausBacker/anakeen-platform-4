<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

class SuiteWorkflow
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        
        $suite->addTestSuite(\Dcp\Pu\TestFormatCollection::class);

        $suite->addTestSuite(\Dcp\Pu\TestExtendProfil::class);
        $suite->addTestSuite(\Dcp\Pu\TestImportFamilyProperty::class);
        $suite->addTestSuite(\Dcp\Pu\TestImportWorkflow::class);
        $suite->addTestSuite(\Dcp\Pu\TestWorflowTransition::class);
        $suite->addTestSuite(\Dcp\Pu\TestImportDocumentsExtra::class);
        $suite->addTestSuite(\Dcp\Pu\TestWorflowMXControl::class);
        // ...
        return $suite;
    }
}
