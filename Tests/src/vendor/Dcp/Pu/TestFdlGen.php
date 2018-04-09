<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

use Anakeen\Core\DocManager;

/**
 * @author  Anakeen
 * @package Dcp\Pu
 */

//require_once 'PU_testcase_dcp_commonfamily.php';

class TestFdlGen extends TestCaseDcpCommonFamily
{
    /**
     * import some documents
     *
     * @static
     * @return array
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_fdlgen.ods"
        );
    }

    /**
     * @dataProvider dataFdlGen
     *
     * @param string $docName
     * @param string $expectValue
     *
     * @return \Doc
     */
    public function testFdlGen($docId, $attrName, $expectedValue)
    {
        $doc = new_doc(self::$dbaccess, $docId);
        $this->assertTrue($doc->isAlive(), sprintf("Document with id '%s' is not alive.", $docId));
        $value = $doc->getRawValue($attrName);
        $this->assertEquals(
            $expectedValue,
            $value,
            sprintf(
                "Unexpected value '%s' for attribute '%s' (expected value is '%s').\nContent of 'FDLGEN/Class.Doc%d.php' = [[[\n%s\n]]]",
                $value,
                $attrName,
                $expectedValue,
                $doc->fromid,
                file_get_contents(DocManager::getDocumentClassFilename($doc->fromname))
            )
        );
    }

    public function dataFdlGen()
    {
        return array(
            array(
                "FDLGEN_EMPTY_1",
                "ATTR_1",
                "GOOD"
            ),
            array(
                "FDLGEN_A_1",
                "ATTR_1",
                "A"
            ),
            array(
                "FDLGEN_A_1",
                "ATTR_2",
                "AA"
            ),
            array(
                "FDLGEN_A_1",
                "ATTR_3",
                "AAA"
            ),
            array(
                "FDLGEN_A_1",
                "ATTR_4",
                "COMMON"
            )
        );
    }
}
