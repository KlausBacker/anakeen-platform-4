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

        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_Contains');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_ContainsValues');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_DocumentTitle');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_FilenameContains');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_HasApplicationTag');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_HasUserTag');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_IdentifiersIn');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_IsEmpty');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_IsEqual');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_IsGreater');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_IsLesser');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_IsNotEmpty');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_IsNotEqual');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_NameEquals');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_OneContains');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_OneDocumentTitle');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_OneEquals');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_OneGreaterThan');
        $suite->addTestSuite('Dcp\Pu\TestDcpDocumentFilter_TitleContains');

        // ...
        return $suite;
    }
}
