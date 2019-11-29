<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;

class TestAttributeDefault extends TestCaseDcpCommonFamily
{
    /**
     * import TST_DEFAULTFAMILY1 family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        self::importConfigurationFile(__DIR__ . "/../../Anakeen/Pu/Config/Inputs/tst_007.struct.xml");
        self::importConfigurationFile(__DIR__ . "/../../Anakeen/Pu/Config/Inputs/tst_008.struct.xml");
        return "PU_data_dcp_familydefault.ods";
    }

    protected $famName = 'TST_DEFAULTFAMILY1';

    /**
     * @dataProvider dataDefaultValues
     */
    public function testDefaultValue($famid, $attrid, $expectedvalue)
    {
        $d = SEManager::createDocument($famid);
        $this->assertTrue(is_object($d), sprintf("cannot create %s document", $famid));

        $struct = SEManager::getFamily($famid);

        $oa = $d->getAttribute($attrid);
        $this->assertNotEmpty($oa, sprintf("attribute %s not found in %s family", $attrid, $famid));
        $value = $d->getRawValue($oa->id);
        $this->assertEquals(
            $expectedvalue,
            $value,
            sprintf(
                "not the expected default value field \"%s\" : in struct has \"%s\"",
                $attrid,
                print_r($struct->getDefValue($attrid), true)
            )
        );
    }

    /**
     * @dataProvider dataInitialParamValues
     */
    public function testInitialParamValue($famid, $attrid, $expectedvalue)
    {
        $d = SEManager::createDocument($famid);
        $this->assertTrue(is_object($d), sprintf("cannot create %s1 document", $famid));

        $oa = $d->getAttribute($attrid);
        $struc = SEManager::getFamily($famid);
        $this->assertNotEmpty($oa, sprintf("attribute %s not found in %s family", $attrid, $famid));
        $value = $d->getFamilyParameterValue($oa->id);
        $this->assertEquals(
            $expectedvalue,
            $value,
            sprintf(
                "not the expected default value attribute \"%s\". Raw is \"%s\"",
                $attrid,
                print_r($struc->getParameterRawValue($attrid), true)
            )
        );
    }

    /**
     * @dataProvider dataDefaultInheritedValues
     */
    public function testDefaultInheritedValue($famid, array $expectedvalues, array $expectedParams)
    {
        $d = SEManager::createDocument($famid);
        $this->assertTrue(is_object($d), sprintf("cannot create %s document", $famid));

        foreach ($expectedvalues as $attrid => $expectedValue) {
            $oa = $d->getAttribute($attrid);
            $this->assertNotEmpty($oa, sprintf("attribute %s not found in %s family", $attrid, $famid));
            $value = $d->getRawValue($oa->id);

            $this->assertEquals(
                $expectedValue,
                $value,
                sprintf("not the expected default value attribute %s", $attrid)
            );
        }
        foreach ($expectedParams as $attrid => $expectedValue) {
            $oa = $d->getAttribute($attrid);
            $this->assertNotEmpty($oa, sprintf("parameter %s not found in %s family", $attrid, $famid));
            $value = $d->getFamilyParameterValue($oa->id);
            $this->assertEquals(
                $expectedValue,
                $value,
                sprintf("not the expected default value parameter %s", $attrid)
            );
        }
    }

    /**
     * @dataProvider dataDefaultInherited
     */
    public function testDefaultInherited($famid, array $expectedvalues, array $expectedParams)
    {
        /**
         * @var  SmartStructure $d
         */
        $d = SEManager::getFamily($famid);
        $this->assertTrue(is_object($d), sprintf("cannot get %s family", $famid));

        foreach ($expectedvalues as $attrid => $expectedValue) {
            $value = $d->getDefValue($attrid);
            $this->assertEquals(
                $expectedValue,
                $value,
                sprintf("not the expected default value attribute %s has %s", $attrid, $d->defaultvalues)
            );
        }
        foreach ($expectedParams as $attrid => $expectedValue) {
            $value = $d->getParameterRawValue($attrid);
            $this->assertEquals(
                $expectedValue,
                $value,
                sprintf("not the expected default value parameter %s", $attrid)
            );
        }
    }

    /**
     * @dataProvider dataFamilyParamRawValue
     */
    public function testFamilyParamRawValue($famid, $default, array $expectedParams)
    {
        /**
         * @var  SmartStructure $d
         */
        $d = SEManager::getFamily($famid);
        $this->assertTrue(is_object($d), sprintf("cannot get %s family", $famid));

        foreach ($expectedParams as $attrid => $expectedValue) {
            $value = $d->getParameterRawValue($attrid, $default);
            $this->assertEquals(
                $expectedValue,
                $value,
                sprintf("not the expected default value parameter %s", $attrid)
            );
        }
    }

    /**
     * @dataProvider dataDocParamvalueInheritedWithDefaultArg
     */
    public function testDocParamvalueInheritedWithDefaultArg($famid, $default, array $expectedParams)
    {
        $d = SEManager::createDocument($famid, false);
        $this->assertTrue(is_object($d), sprintf("cannot create \"%s\" document", $famid));

        foreach ($expectedParams as $attrid => $expectedvalue) {
            $value = $d->getFamilyParameterValue($attrid, $default);
            $this->assertEquals(
                $expectedvalue,
                $value,
                sprintf("not the expected default value parameter \"%s\"", $attrid)
            );
        }
    }

    /**
     * @dataProvider dataWrongValue
     */
    public function testWrongValue($famid, $errorCode)
    {
        $err = '';
        try {
            SEManager::createDocument($famid);
            $this->assertNotEmpty($err, sprintf(" no error returned, must have %s", $errorCode));
        } catch (\Anakeen\Exception $e) {
            $err = $e->getDcpCode();
            $this->assertEquals($errorCode, $err, sprintf("not the good error code : %s", $e->getMessage()));
        }
    }

    /**
     * @dataProvider dataInitialParam
     */
    public function testInitialParam($famid, $attrid, $expectedValue)
    {
        $d = SEManager::createDocument($famid);
        $value = $d->getFamilyParameterValue($attrid);
        $f = $d->getFamilyDocument();
        $this->assertEquals(
            $expectedValue,
            $value,
            sprintf(
                "parameter %s has not correct initial value, family has \"%s\"",
                $attrid,
                $f->param . $f->getParameterRawValue($attrid)
            )
        );
    }

    public function dataInitialParam()
    {
        return array(
            array(
                "TST_DEFAULTFAMILY2",
                "TST_P4",
                40,
            ),
            array(
                "TST_DEFAULTFAMILY2",
                "TST_P5",
                50
            ),
            array(
                "TST_DEFAULTFAMILY3",
                "TST_P5",
                51
            )
        );
    }

    public function dataFamilyParamRawValue()
    {
        return array(
            array(
                "TST_DEFAULTFAMILY2",
                "34",
                array(
                    'TST_P1' => 'PFirst',
                    "TST_P2" => "10",
                    "TST_P3" => "::oneMore(TST_P2)",
                    "TST_P4" => "40",
                    'TST_P6' => '34'
                )
            )
        );
    }

    public function dataDocParamvalueInheritedWithDefaultArg()
    {
        return array(
            array(
                "TST_DEFAULTFAMILY2",
                "341",
                array(
                    "TST_P0" => "341",
                    'TST_P1' => 'PFirst',
                    "TST_P2" => "10",
                    "TST_P3" => "11",
                    "TST_P4" => "40",
                    'TST_P6' => '{20}',
                    'TST_P7' => '{21}'
                )
            ),
            array(
                "TST_DEFAULTFAMILY4",
                "341",
                array(
                    "TST_P0" => "341",
                    'TST_P1' => 'PThird',
                    "TST_P2" => "10",
                    "TST_P3" => "11",
                    "TST_P4" => "40",
                    'TST_P6' => '{20}',
                    'TST_P7' => '{21}'
                )
            )
        );
    }

    public function dataWrongValue()
    {
        return array(
            array(
                "TST_DEFAULTFAMILY7",
                "DFLT0009"
            ),
            array(
                "TST_DEFAULTFAMILY8",
                "DFLT0009"
            ),
            array(
                "TST_DEFAULTFAMILY9",
                "DFLT0008"
            )
        );
    }

    public function dataDefaultInherited()
    {
        return array(
            array(
                "TST_DEFAULTFAMILY2",
                array(
                    "TST_TITLE" => "First",
                    "TST_NUMBER1" => "::isOne()",
                    "TST_NUMBER2" => "::oneMore(TST_NUMBER1)",
                    "TST_NUMBER3" => "::oneMore(2)"
                ),
                array(
                    'TST_P1' => 'PFirst',
                    "TST_P2" => "10",
                    "TST_P3" => "::oneMore(TST_P2)"
                )
            ),
            array(
                "TST_DEFAULTFAMILY3",
                array(
                    "TST_TITLE" => "Second",
                    "TST_NUMBER1" => "::isOne()",
                    "TST_NUMBER2" => "::simpleAdd(12,TST_NUMBER1)",
                    "TST_NUMBER3" => "::oneMore(2)"
                ),
                array(
                    'TST_P1' => 'PSecond',
                    "TST_P2" => "10",
                    "TST_P3" => "::oneMore(TST_P2)"
                )
            ),
            array(
                "TST_DEFAULTFAMILY4",
                array(
                    "TST_TITLE" => "Third",
                    "TST_NUMBER1" => "::isOne()",
                    "TST_NUMBER2" => "::oneMore(TST_NUMBER1)",
                    "TST_NUMBER3" => ""
                ),
                array(
                    'TST_P1' => 'PThird',
                    "TST_P2" => "10",
                    "TST_P3" => "::oneMore(TST_P2)"
                )
            )
        );
    }

    public function dataDefaultInheritedValues()
    {
        return array(
            array(
                "TST_DEFAULTFAMILY2",
                array(
                    "TST_TITLE" => "First",
                    "TST_NUMBER1" => "1",
                    "TST_NUMBER2" => "2",
                    "TST_NUMBER3" => "3"
                ),
                array(
                    'TST_P1' => 'PFirst',
                    "TST_P2" => "10",
                    "TST_P3" => "11",
                    'TST_P6' => '{20}',
                    'TST_P7' => '{21}'
                )
            ),
            array(
                "TST_DEFAULTFAMILY3",
                array(
                    "TST_TITLE" => 'Second',
                    "TST_NUMBER1" => "1",
                    "TST_NUMBER2" => "13",
                    "TST_NUMBER3" => "3"
                ),
                array(
                    "TST_P1" => 'PSecond',
                    "TST_P2" => "10",
                    "TST_P3" => "11",
                    'TST_P6' => '{20}',
                    'TST_P7' => '{21}'
                )
            ),
            array(
                "TST_DEFAULTFAMILY4",
                array(
                    "TST_TITLE" => 'Third',
                    "TST_NUMBER1" => "1",
                    "TST_NUMBER2" => "2",
                    "TST_NUMBER3" => ""
                ),
                array(
                    "TST_P1" => 'PThird',
                    "TST_P2" => "10",
                    "TST_P3" => "11",
                    'TST_P6' => '{20}',
                    'TST_P7' => '{21}'
                )
            )
        );
    }

    public function dataDefaultValues()
    {
        return array(
            array(
                "TST_DEFAULTFAMILY1",
                'TST_TITLE',
                'The title'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_NUMBER0',
                '0'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_NUMBER1',
                '1'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_NUMBER2',
                '2'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_NUMBER3',
                '3'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_NUMBER4',
                '4'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_NUMBER5',
                '5'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_NUMBER6',
                '50'
            ),

            array(
                "TST_DEFAULTFAMILY1",
                'TST_NUMBER7',
                '53'
            ),

            array(
                "TST_DEFAULTFAMILY1",
                'TST_NUMBER8',
                '6'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_NUMBER9',
                '11'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_TEXT1',
                'TST_TITLE'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_TEXT2',
                'TST_TITLE'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_TEXT3',
                'TST_TITLE,TST_TITLE'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_TEXT4',
                'it is,simple word,testing'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_TEXT5',
                'it\'s,a "citation",and "second"'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_TEXT6',
                '[:TST_TITLE:]'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_TEXT7',
                "0"
            ),
            array(
                "TST_DEFAULTFAMILY5",
                'TST_TITLE',
                "First"
            ),
            array(
                "TST_DEFAULTFAMILY5",
                'TST_NUMBER1',
                "{1}"
            ),
            array(
                "TST_DEFAULTFAMILY5",
                'TST_TEXT1',
                '{"cellule un"}'
            ),
            array(
                "TST_DEFAULTFAMILY5",
                'TST_NUMBER2',
                ""
            ),
            array(
                "TST_DEFAULTFAMILY5",
                'TST_TEXT2',
                ""
            ),
            array(
                "TST_DEFAULTFAMILY6",
                'TST_TEXT1',
                "{Un,Deux}"
            ),
            array(
                "TST_DEFAULTFAMILY6",
                'TST_TEXT2',
                "{First,Second}"
            ),
            array(
                "TST_DEFAULTFAMILY6",
                'TST_NUMBER2',
                "{10,20}"
            ),
            array(
                "TST_DEFAULTFAMILY6",
                'TST_DOCM2',
                "{{9,11},{12,13}}"
            ),
            array(
                "TST_DEFAULTFAMILYNAMESPACE",
                "TEXTE",
                "one"
            ),
            array(
                "TST_007",
                "tst_title",
                "Hello"
            ),
            array(
                "TST_007",
                "tst_n0",
                "0"
            ),
            array(
                "TST_007",
                "tst_n1",
                "1"
            ),
            array(
                "TST_007",
                "tst_n2",
                "2"
            ),
            array(
                "TST_007",
                "tst_n6",
                "6"
            ),
            array(
                "TST_007",
                "tst_n7",
                "7"
            ),
            array(
                "TST_007",
                "tst_t0",
                "Hello World : C'est l'été."
            ),
            array(
                "TST_007",
                "tst_t1",
                "Des œufs à 12€,\net des brouettes."
            ),
            array(
                "TST_007",
                "tst_t2",
                "Début,Hello World : C'est l'été.,Fin"
            ),
            array(
                "TST_007",
                "tst_t3",
                "[:Début,Hello World : C'est l'été.,Fin:]"
            ),
            array(
                "TST_007",
                "tst_t4",
                "Quatre"
            ),
            array(
                "TST_007",
                "tst_e0",
                "{red,green}"
            ),
            array(
                "TST_007",
                "tst_ts",
                "{Hola,Hombre}"
            ),
            array(
                "TST_007",
                "tst_is",
                "{12,56}"
            ),
            array(
                "TST_007",
                "tst_lts",
                '{"Loooong texte"}'
            ),
            array(
                "TST_007",
                "tst_ds",
                '{12.567}'
            ),
            array(
                "TST_007a",
                "tst_title",
                "Hello"
            ),
            array(
                "TST_007a",
                "tst_ts",
                "{Hola,Hombre}"
            ),
            array(
                "TST_007a",
                "tst_is",
                "{12,56}"
            ),
            array(
                "TST_007a",
                "tst_ds",
                ""
            ),
            array(
                "TST_007a",
                "tst_lts",
                ''
            ),
            array(
                "TST_007a",
                "tst_n0",
                "1"
            ),
            array(
                "TST_007a",
                "tst_n1",
                "2"
            ),
            array(
                "TST_007a",
                "tst_n2",
                "3"
            ),
            array(
                "TST_007a",
                "tst_n6",
                "6"
            ),
            array(
                "TST_007a",
                "tst_n7",
                "9"
            ),
            array(
                "TST_007a",
                "tst_t0",
                "Hello World : C'est l'hivers."
            ),
            array(
                "TST_007a",
                "tst_t1",
                "Des œufs à 14€,\nça a augmenté."
            ),
            array(
                "TST_007a",
                "tst_t2",
                "Début,Hello World : C'est l'hivers.,Fin"
            ),
            array(
                "TST_007a",
                "tst_t3",
                "[:Début,Hello World : C'est l'hivers.,Fin:]"
            ),
            array(
                "TST_007a",
                "tst_t4",
                ""
            ),

        );
    }

    public function dataInitialParamValues()
    {
        return array(
            array(
                "TST_DEFAULTFAMILY1",
                'TST_P1',
                'test one'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_P2',
                '10'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_P3',
                '11'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_P4',
                '12'
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_P6',
                "{124}"
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_P7',
                "{591}"
            ),
            array(
                "TST_DEFAULTFAMILY1",
                'TST_P8',
                "{456}"
            ),
            array(
                "TST_DEFAULTFAMILYNAMESPACE",
                "P_TEXTE",
                "one"
            ),
            array(
                "TST_008",
                "tst_p0",
                "{red}"
            ),
            array(
                "TST_008",
                "tst_p1",
                "23"
            ),
            array(
                "TST_008",
                "tst_p2",
                23 + 28
            ),
            array(
                "TST_008",
                "tst_p3s",
                '{Hola,Hombre}'
            ),
            array(
                "TST_008",
                "tst_p4s",
                '{122,156}'
            ),
            array(
                "TST_008",
                "tst_p5s",
                '{Hello}'
            ),
            array(
                "TST_008",
                "tst_p6s",
                '{Hola,Bienvenido}'
            ),
            array(
                "TST_008a",
                "tst_p0",
                "{red}"
            ),
            array(
                "TST_008a",
                "tst_p1",
                "78"
            ),
            array(
                "TST_008a",
                "tst_p2",
                78 + 28
            ),
            array(
                "TST_008a",
                "tst_p3s",
                '{Hola,Hombre}'
            ),
            array(
                "TST_008a",
                "tst_p4s",
                '{122,156}'
            ),
            array(
                "TST_008a",
                "tst_p5s",
                '{Hello}'
            ),

            array(
                "TST_008b",
                "tst_p0",
                "{red}"
            ),
            array(
                "TST_008b",
                "tst_p1",
                "78"
            ),
            array(
                "TST_008b",
                "tst_p2",
                78 + 28
            ),
            array(
                "TST_008b",
                "tst_p3s",
                '{Hola,Hombre}'
            ),
            array(
                "TST_008b",
                "tst_p4s",
                '{122,156}'
            ),
            array(
                "TST_008b",
                "tst_p5s",
                '{Hello}'
            )
        );
    }
}
