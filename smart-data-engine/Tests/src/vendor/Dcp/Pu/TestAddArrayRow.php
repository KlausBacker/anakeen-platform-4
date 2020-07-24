<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

use Anakeen\Core\SEManager;
use Anakeen\SmartElementManager;

/**
 * @author Anakeen
 * @package Dcp\Pu
 */

//require_once 'PU_testcase_dcp_commonfamily.php';
class TestAddArrayRow extends TestCaseDcpCommonFamily
{
    /**
     * import TST_UPDTATTR
     * @static
     * @return array|string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_addArrayRow_family.csv"
        );
    }

    /**
     * @dataProvider dataExecuteAddArrayRowError
     * @param $data
     * @throws \Anakeen\Exception
     */
    public function testExecuteAddArrayRowError($data)
    {
        $doc = SmartElementManager::createDocument($data['fam']);
        $this->assertTrue(is_object($doc), sprintf("Could not create new document from family '%s'.", $data['fam']));

        foreach ($data['rows'] as $row) {
            try {
                $err = $doc->addArrayRow($data['array_attr_name'], $row['data'], $row['index']);
                $this->assertEmpty("This test have an error, but not passed throw any error");
            } catch (\Anakeen\Exception $error) {
                if ($error->getDcpCode() === $row['code']) {
                    $this->assertNotEmpty($error->getMessage(), "This test is good, we have an error");
                } else {
                    $this->assertNotEmpty($error->getMessage(), "We found an error with invalid error Code");
                }
            } catch (\Exception $error) {
                $this->assertNotEmpty($error->getMessage(), "In this test we don't get an Anakeen Exception");
            }
        }
    }

    /**
     * @dataProvider dataAddArrayRow
     * @param $data
     * @throws \Anakeen\Exception
     */
    public function testExecuteAddArrayRow($data)
    {
        $doc = SmartElementManager::createDocument($data['fam']);
        $this->assertTrue(is_object($doc), sprintf("Could not create new document from family '%s'.", $data['fam']));

        $err = $doc->add();
        $this->assertEmpty($err, sprintf("Error adding new document in database: %s", $err));


        $err = $doc->setLogicalName($data['name']);
        $this->assertEmpty($err, sprintf("Error setting logical identifier '%s' on new document: %s", $data['name'], $err));

        if (!empty($data["login"])) {
            $doc->disableAccessControl(false);
            $this->sudo($data["login"]);
        }
        foreach ($data['rows'] as $row) {
            $err = $doc->addArrayRow($data['array_attr_name'], $row['data'], $row['index']);
            $this->assertEmpty($err, sprintf("Error adding row {%s} to '%s': %s", join(', ', $row['data']), $data['name'], $err));
        }
        $this->assertTrue($doc->isChanged(), sprintf("no changed value detected"));
        $err = $doc->store();
        $this->assertEmpty($err, sprintf("modify() on '%s' returned with error: %s", $data['name'], $err));

        self::resetDocumentCache();

        $doc = SEManager::getDocument($data['name']);
        $this->assertTrue(is_object($doc), sprintf("Error retrieving document '%s': %s", $data['name'], $err));

        foreach ($data['expected_tvalues'] as $colName => $colData) {
            $tvalue = $doc->getMultipleRawValues($colName);
            $this->assertTrue(is_array($tvalue), sprintf("getMultipleRawValues(%s) on document '%s' did not returned an array.", $colName, $data['name']));

            $tvalueCount = count($tvalue);
            $expectedCount = count($colData);
            $this->assertTrue(
                ($tvalueCount == $expectedCount),
                sprintf(
                    "Column size mismatch on column '%s' from document '%s' (actual size is '%s', while expecting '%s').",
                    $colName,
                    $data['name'],
                    $tvalueCount,
                    $expectedCount
                )
            );

            foreach ($colData as $i => $expectedCellContent) {
                $tvalueCellContent = $tvalue[$i];
                $this->assertTrue(($tvalueCellContent == $expectedCellContent), sprintf(
                    "Cell content '%s' did not matched expected content '%s' (document '%s' / column '%s' / line '%s' / column cells {%s})",
                    $tvalueCellContent,
                    $expectedCellContent,
                    $data['name'],
                    $colName,
                    $i,
                    join(', ', $tvalue)
                ));
            }
        }
        unset($colData);
        if (!empty($data["login"])) {
            $this->exitSudo();
        }
    }

    public function dataExecuteAddArrayRowError()
    {
        return array(
            array(
                array(
                    'fam' => 'TST_ADDARRAYROW',
                    'name' => 'TST_ADDARRAYROW_CORE0105',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'code' => "CORE0105",
                            'index' => -1,
                            'data' => array(
                                'COL_1' => '1_1',
                                'COL_2' => (object)'test not scalar',
                                'COL_3' => '1_3',
                                'COL_4' => '1_4'
                            )
                        ),
                    ),
                ),
            ),
            array(
                array(
                    'fam' => 'TST_ADDARRAYROW',
                    'name' => 'TST_ADDARRAYROW_CORE0106',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'code' => "CORE0106",
                            'index' => -1,
                            'data' => array(
                                'COL_1' => array(
                                    array(
                                        array(
                                            array()
                                        )
                                    )
                                )
                            )
                        ),
                    ),
                ),
            ),
            array(
                array(
                    'fam' => 'TST_ADDARRAYROW',
                    'name' => 'TST_ADDARRAYROW_CORE0107',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'code' => "CORE0107",
                            'index' => -1,
                            'data' => "test"
                        ),
                    ),
                ),
            ),
            array(
                array(
                    'fam' => 'TST_ADDARRAYROW',
                    'name' => 'TST_ADDARRAYROW_CORE0108',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'code' => "CORE0108",
                            'index' => -1,
                            'data' => array(
                                "testError" => "testError"
                            )
                        ),
                    ),
                ),
            )
        );
    }

    public function dataAddArrayRow()
    {
        return array(
            array(
                array(
                    'fam' => 'TST_ADDARRAYROW',
                    'name' => 'TST_ADDARRAYROW_DOC_01',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'index' => -1,
                            'data' => array(
                                'COL_1' => '1_1',
                                'COL_2' => '1_2',
                                'COL_3' => '1_3',
                                'COL_4' => '1_4'
                            )
                        ),
                        array(
                            'index' => -1,
                            'data' => array(
                                'cOl_1' => '2_1'
                            )
                        ),
                        array(
                            'index' => -1,
                            'data' => array()
                        ),
                        array(
                            'index' => -1,
                            'data' => array(
                                'col_2' => '4_2',
                                'col_3' => '4_3',
                                'col_4' => '4_4'
                            )
                        )
                    ),
                    'expected_tvalues' => array(
                        'col_1' => array(
                            '1_1',
                            '2_1',
                            '',
                            ''
                        ),
                        'col_2' => array(
                            '1_2',
                            '',
                            '',
                            '4_2'
                        ),
                        'col_3' => array(
                            '1_3',
                            '',
                            '',
                            '4_3'
                        ),
                        'col_4' => array(
                            '1_4',
                            '',
                            '',
                            '4_4'
                        )
                    )
                )
            ),
            array(
                array(
                    'fam' => 'TST_ADDARRAYROW',
                    'name' => 'TST_ADDARRAYROW_DOC_02',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'index' => 0,
                            'data' => array(
                                'col_2' => '4_2',
                                'col_3' => '4_3',
                                'col_4' => '4_4'
                            )
                        ),
                        array(
                            'index' => 0,
                            'data' => array()
                        ),
                        array(
                            'index' => 0,
                            'data' => array(
                                'cOl_1' => '2_1'
                            )
                        ),
                        array(
                            'index' => 0,
                            'data' => array(
                                'COL_1' => '1_1',
                                'COL_2' => '1_2',
                                'COL_3' => '1_3',
                                'COL_4' => '1_4'
                            )
                        )
                    ),
                    'expected_tvalues' => array(
                        'col_1' => array(
                            '1_1',
                            '2_1',
                            '',
                            ''
                        ),
                        'col_2' => array(
                            '1_2',
                            '',
                            '',
                            '4_2'
                        ),
                        'col_3' => array(
                            '1_3',
                            '',
                            '',
                            '4_3'
                        ),
                        'col_4' => array(
                            '1_4',
                            '',
                            '',
                            '4_4'
                        )
                    )
                )
            ),
            array(
                array(
                    'fam' => 'TST_ADDARRAYROW',
                    'name' => 'TST_ADDARRAYROW_DOC_03',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'index' => -1,
                            'data' => array(
                                'col_2' => '2_2',
                                'col_3' => '2_3',
                                'col_4' => '2_4'
                            )
                        ),
                        array(
                            'index' => 1,
                            'data' => array()
                        ),
                        array(
                            'index' => 2,
                            'data' => array(
                                'cOl_1' => '4_1'
                            )
                        ),
                        array(
                            'index' => 0,
                            'data' => array(
                                'COL_1' => '1_1',
                                'COL_2' => '1_2',
                                'COL_3' => '1_3',
                                'COL_4' => '1_4'
                            )
                        )
                    ),
                    'expected_tvalues' => array(
                        'col_1' => array(
                            '1_1',
                            '',
                            '',
                            '4_1'
                        ),
                        'col_2' => array(
                            '1_2',
                            '2_2',
                            '',
                            ''
                        ),
                        'col_3' => array(
                            '1_3',
                            '2_3',
                            '',
                            ''
                        ),
                        'col_4' => array(
                            '1_4',
                            '2_4',
                            '',
                            ''
                        )
                    )
                )
            ),
            array(
                array(
                    'fam' => 'TST_ADDARRAYROW_DEV_5361',
                    'name' => 'TST_ADDARRAYROW_DEV_5361_DOC_01',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'index' => -1,
                            'data' => array(
                                'col_1' => 'Line 1, Col 1',
                                'col_2' => ''
                            )
                        ),
                        array(
                            'index' => -1,
                            'data' => array(
                                'col_1' => '',
                                'col_2' => 'Line 2, Col 2'
                            )
                        ),
                    ),
                    'expected_tvalues' => array(
                        'col_1' => array(
                            'Line 1, Col 1',
                            ''
                        ),
                        'col_2' => array(
                            '',
                            'Line 2, Col 2'
                        )
                    )
                )
            ),

            array(
                array(
                    "login" => "iuser_aar1",
                    'fam' => 'TST_ADDARRAYROW_DOCID',
                    'name' => 'TST_ADDARRAYROW_DOCID_01',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'index' => -1,
                            'data' => array(
                                'index' => '1',
                            )
                        ),
                        array(
                            'index' => -1,
                            'data' => array(
                                'index' => '2',
                            )
                        ),
                    ),
                    'expected_tvalues' => array(
                        'index' => array(
                            '1',
                            '2'
                        ),
                        'rel1' => array(
                            '',
                            ''
                        )
                    )
                )
            ),

            array(
                array(
                    "login" => "iuser_aar1",
                    'fam' => 'TST_ADDARRAYROW_DOCID',
                    'name' => 'TST_ADDARRAYROW_DOCID_02',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'index' => -1,
                            'data' => array(
                                'rel1' => 'TST_AAR1',
                            )
                        ),
                        array(
                            'index' => -1,
                            'data' => array(
                                'rel1' => 'TST_AAR2',
                            )
                        ),
                    ),
                    'expected_tvalues' => array(
                        'rel1_title' => array(
                            'Hello',
                            'World'
                        )
                    )
                )
            )
        );
    }
}
