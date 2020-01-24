<?php

namespace Dcp\Pu;

/**
 * @author  Anakeen
 * @package Dcp\Pu
 */
class SuiteDcpFilter
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');

        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterContains');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterContainsValues');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterDocumentTitle');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterFilenameContains');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterHasApplicationTag');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterHasUserTag');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterIdentifiersIn');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterIsEmpty');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterIsEqual');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterIsGreater');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterIsLesser');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterIsNotEmpty');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterIsNotEqual');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterNameEquals');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterOneContains');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterOneDocumentTitle');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterOneEquals');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterOneGreaterThan');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterTitleContains');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilterOrOperator');

        // ...
        return $suite;
    }
}
