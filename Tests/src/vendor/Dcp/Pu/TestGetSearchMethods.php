<?php

namespace Dcp\Pu;

use Anakeen\Core\SEManager;
use Anakeen\SmartStructures\Dsearch\DSearchHooks;

class TestGetSearchMethods extends TestCaseDcpCommonFamily
{
    /**
     * import TST_UPDTATTR
     * @static
     * @return array|string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_getSearchMethods.ods"
        );
    }

    /**
     * Test getSearchMethods() method
     *
     * @dataProvider dataGetSearchMethods
     * @param $famid
     * @param $attrid
     * @param $type
     * @param $hasMethods
     * @internal     param $data
     */
    public function testGetSearchMethods($famid, $attrid, $type, $hasMethods)
    {
        $tmpDoc = SEManager::createTemporaryDocument($famid);
        $this->assertTrue(is_object($tmpDoc), sprintf("Error creating temorary document from family '%s'.", $famid));

        $methodList = $tmpDoc->getSearchMethods($attrid, $type);
        $this->assertTrue((count($methodList) > 0), sprintf("Empty method list for attribute '%s' with type '%s' from family '%s'.", $attrid, $type, $famid));

        $methodNameList = array_map(function ($elmt) {
            return $elmt['method'];
        }, $methodList);

        foreach ($hasMethods as $methodName) {
            $this->assertTrue(in_array($methodName, $methodNameList), sprintf("Expected method '%s' not found in returned methods (%s)", $methodName, join(', ', $methodNameList)));
        }
    }

    public function dataGetSearchMethods()
    {
        return array(
            array(
                'TST_GETSEARCHMETHODS',
                's_date',
                'date',
                array(
                    '::getDate(-1)',
                    '::getDate()',
                    '::getDate(1)'
                )
            ),
            array(
                'TST_GETSEARCHMETHODS',
                's_timestamp',
                'timestamp',
                array(
                    '::getDate(-1)',
                    '::getDate()',
                    '::getDate(1)'
                )
            ),
            array(
                'TST_GETSEARCHMETHODS_OVERRIDE',
                's_date',
                'date',
                array(
                    '::getDate(-365)',
                    '::getDate(-1)',
                    '::getDate()',
                    '::getDate(1)',
                    '::getDate(365)'
                )
            ),
            array(
                'TST_GETSEARCHMETHODS_OVERRIDE',
                's_text',
                'text',
                array(
                    '::getFoo()'
                )
            ),
            array(
                'TST_GETSEARCHMETHODS_OVERRIDE',
                's_double',
                'double("%.02f")',
                array(
                    '::getTwoCents()'
                )
            )
        );
    }

    /**
     * Test invalid/non explicitly declared search methods
     *
     * @dataProvider dataInvalidSearchMethod
     * @param string $dSearchId
     */
    public function testInvalidSearchMethod($dSearchId)
    {
        /**
         * @var DSearchHooks $dSearch
         */

        $dSearch = SEManager::getDocument($dSearchId);
        $this->assertTrue($dSearch->isAlive(), sprintf("dSearch with id '%s' is not alive.", $dSearchId));
        $sql = $dSearch->getSqlDetailFilter();
        $this->assertTrue(($sql == 'false'), sprintf("getSqlDetailFilter() did not returned (string)'false' (returned value is '%s').", $sql));
    }

    public function dataInvalidSearchMethod()
    {
        return array(
            array(
                'DSEARCH_TST_GETSEARCHMETHODS_1'
            ),
            array(
                'DSEARCH_TST_GETSEARCHMETHODS_2'
            )
        );
    }
}
