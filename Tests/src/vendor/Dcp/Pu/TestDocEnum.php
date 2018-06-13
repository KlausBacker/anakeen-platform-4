<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

use Anakeen\Core\EnumManager;
use Anakeen\Core\SmartStructure\DocEnum;
use Anakeen\Core\SmartStructure\EnumLocale;
use Anakeen\Core\SmartStructure\EnumStructure;

/**
 * @author  Anakeen
 * @package Dcp\Pu
 */
//require_once 'PU_testcase_dcp_commonfamily.php';

class TestDocEnum extends TestCaseDcpCommonFamily
{
    const familyName = "TST_DOCENUM";

    /**
     * import TST_DOCENUM family
     * @static
     * @return string[]
     */
    protected static function getConfigFile()
    {
        return ["PU_data_dcp_docenum_family.xml"];
    }

    public static function tearDownAfterClass()
    {
        $langs = array(
            "fr_FR",
            "en_US"
        );
        foreach ($langs as $lang) {
            $moFile = DocEnum::getMoFilename("TST_DOCENUM-0123", $lang);
            unlink($moFile);
        }
        \LibSystem::reloadLocaleCache();
        parent::tearDownAfterClass();
    }

    /**
     * @dataProvider dataDocEnumAdd
     */
    public function testDocEnumAdd($attrid, $key, $label, $absOrder, $expectedOrder)
    {
        //print_r(EnumManager::getEnum($attrid));exit;
       $enums= EnumManager::getEnums($attrid);
        $s = new EnumStructure();
        $s->key = $key;
        $s->label = $label;
        $s->absoluteOrder = $absOrder;

        DocEnum::addEnum($attrid, $s);

        $this->verifyEnumProperties($attrid, $key, $label, $expectedOrder);
    }

    /**
     * @dataProvider dataDocEnumMod
     */
    public function testDocEnumMod($attrid, $key, $label, $absOrder, $expectedOrder)
    {
        $s = new EnumStructure();
        $s->key = $key;
        $s->label = $label;
        $s->absoluteOrder = $absOrder;

        DocEnum::modifyEnum($attrid, $s);

        $this->verifyEnumProperties($attrid, $key, $label, $expectedOrder);
    }

    /**
     * @dataProvider dataDocAddRelativeOrder
     */
    public function testDocEnumAddRelativeOrder($attrid, $key, $label, $relativeOrder, $expectedOrder)
    {
        $s = new EnumStructure();
        $s->key = $key;
        $s->label = $label;
        $s->orderBeforeThan = $relativeOrder;

        DocEnum::addEnum($attrid, $s);

        $this->verifyEnumProperties($attrid, $key, $label, $expectedOrder);
    }

    /**
     * @dataProvider dataDocEnumDisabled
     */
    public function testDocEnumDisabled($enumName, array $disabledKeys, $expectedEnums)
    {
        foreach ($disabledKeys as $key) {
            $oe = new DocEnum("", array(
                $enumName,
                $key
            ));

            $this->assertTrue($oe->isAffected(), "enum  $enumName : $key not found");
            $s = new EnumStructure();
            $s->key = $oe->key;
            $s->label = $oe->label;
            $s->absoluteOrder = $oe->eorder;
            $s->disabled = true;

            DocEnum::modifyEnum($enumName, $s);
        }
        /**
         * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
         */
        EnumManager::resetEnum();
        $enums = EnumManager::getEnums($enumName, false);
        $enumKey = array_keys($enums);
        $diff = array_diff($enumKey, $expectedEnums);

        $this->assertEmpty($diff, sprintf("getEnumLabel:: not expected visible \nExpect:\n %s,\nHas\n %s", print_r($expectedEnums, true), print_r($enumKey, true)));

        $this->assertEquals(count($expectedEnums), count($enumKey), sprintf("getEnumLabel:: not expected enum count %s, %s", print_r($expectedEnums, true), print_r($enums, true)));

        EnumManager::resetEnum();
        $enums = EnumManager::getEnums($enumName, false);
        $enums2 = EnumManager::getEnums($enumName, false);
        $this->assertEquals($enums, $enums2, sprintf("getEnum::not same with cache %s, %s", print_r($enums, true), print_r($enums2, true)));

        $enumKey = array_keys($enums);
        $diff = array_diff($enumKey, $expectedEnums);

        $this->assertEmpty($diff, sprintf("getEnum:: not expected visible \nExpect:\n %s,\nHas\n %s", print_r($expectedEnums, true), print_r($enumKey, true)));

        $this->assertEquals(count($expectedEnums), count($enumKey), sprintf("getEnum::not expected enum count %s, %s", print_r($expectedEnums, true), print_r($enums, true)));
    }

    /**
     * @dataProvider dataDocModRelativeOrder
     */
    public function testDocEnumModRelativeOrder($attrid, $key, $label, $relativeOrder, $expectedOrder)
    {
        $s = new EnumStructure();
        $s->key = $key;
        $s->label = $label;
        $s->orderBeforeThan = $relativeOrder;

        DocEnum::modifyEnum($attrid, $s);

        $this->verifyEnumProperties($attrid, $key, $label, $expectedOrder);
    }

    private function verifyEnumProperties($enumName, $key, $expectedLabel, $expectedOrder)
    {
        EnumManager::resetEnum();

        $elabel = EnumManager::getEnumItem($enumName, $key);

        $this->assertTrue($elabel !== null, "Enum not inserted");
        $this->assertEquals($expectedLabel, $elabel["label"], "Enum incorrect label");

        $de = new DocEnum("", array(
            $enumName,
            $key
        ));
        $this->assertTrue($de->isAffected(), "Enum record not found");
        $this->assertEquals($expectedOrder, $de->eorder, "Enum order not the good one");
    }

    /**
     * @dataProvider dataDocEnumAddLocale
     */
    public function testDocEnumAddLocale($attrid, $key, $label, array $locale)
    {
        $s = new EnumStructure();
        $s->key = $key;
        $s->label = $label;

        foreach ($locale as $lang => $localeLabel) {
            $s->localeLabel[] = new EnumLocale($lang, $localeLabel);
        }

        DocEnum::addEnum($attrid, $s);
        \LibSystem::reloadLocaleCache();

        EnumManager::resetEnum();
        $label = EnumManager::getEnumItem($attrid, $key)["label"];
        $this->assertTrue($label !== null, "Enum not inserted");

        foreach ($locale as $lang => $localeLabel) {
            \Anakeen\Core\ContextManager::setLanguage($lang);
            EnumManager::resetEnum();
            $eLabel = EnumManager::getEnumItem($attrid, $key)["label"];
            $this->assertEquals($localeLabel, $eLabel, sprintf("not good %s label", $lang));
        }
    }

    public function dataDocEnumAdd()
    {
        return array(
            array(
                "attrid" => "TST_DOCENUM-0123",
                "key" => 4,
                "label" => "quatro",
                "order" => 4,
                "expectedOrder" => 4
            ),
            array(
                "attrid" => "TST_DOCENUM-0123",
                "key" => 5,
                "label" => "cinqo",
                "order" => 1,
                "expectedOrder" => 1
            ),
            array(
                "attrid" => "TST_DOCENUM-0123",
                "key" => 50,
                "label" => "mucho",
                "order" => 50,
                "expectedOrder" => 5
            ),
            array(
                "attrid" => "TST_DOCENUM-0123",
                "key" => 500,
                "label" => "mucho mucho",
                "order" => 0,
                "expectedOrder" => 5
            ),
            array(
                "attrid" => "TST_DOCENUM-0123",
                "key" => 400,
                "label" => "mucho mucho",
                "order" => -1,
                "expectedOrder" => 5
            )
        );
    }

    public function dataDocEnumMod()
    {
        return array(
            array(
                "attrid" => "TST_DOCENUM-0123",
                "key" => 1,
                "label" => "one more",
                "order" => 4,
                "expectedOrder" => 3
            ),
            array(
                "attrid" => "TST_DOCENUM-0123",
                "key" => 1,
                "label" => "one more",
                "order" => 0,
                "expectedOrder" => 4
            )
        );
    }

    public function dataDocAddRelativeOrder()
    {
        return array(
            array(
                "attrid" => "TST_DOCENUM-ABCD",
                "key" => "a1",
                "label" => "one more",
                "before" => "b",
                "expectedOrder" => 2
            ),
            array(
                "attrid" => "TST_DOCENUM-ABCD",
                "key" => "a0",
                "label" => "one more",
                "before" => "a",
                "expectedOrder" => 1
            ),
            array(
                "attrid" => "TST_DOCENUM-ABCD",
                "key" => "a0",
                "label" => "one more",
                "before" => "d",
                "expectedOrder" => 6
            ),
            array(
                "attrid" => "TST_DOCENUM-ABCD",
                "key" => "a0",
                "label" => "one more",
                "before" => "",
                "expectedOrder" => 7
            )
        );
    }

    public function dataDocModRelativeOrder()
    {
        return array(
            array(
                "attrid" => "TST_DOCENUM-ABCD",
                "key" => "b",
                "label" => "one more",
                "before" => "b",
                "expectedOrder" => 2
            ),
            array(
                "attrid" => "TST_DOCENUM-ABCD",
                "key" => "d",
                "label" => "one more",
                "before" => "a",
                "expectedOrder" => 1
            ),
            array(
                "attrid" => "TST_DOCENUM-ABCD",
                "key" => "a",
                "label" => "one more",
                "before" => "",
                "expectedOrder" => 6
            )
        );
    }

    public function dataDocEnumDisabled()
    {
        return array(
            array(
                "attrid" => "TST_DOCENUM-0123",
                "disable" => array(
                    "1"
                ),
                "expect" => array(
                    " ",
                    "0",
                    "2"
                )
            ),
            array(
                "attrid" => "TST_DOCENUM-0123",
                "disable" => array(),
                "expect" => array(
                    " ",
                    "0",
                    "1",
                    "2"
                )
            ),
            array(
                "attrid" => "TST_DOCENUM-0123",
                "disable" => array(
                    " ",
                    "0",
                    "1",
                    "2"
                ),
                "expect" => array()
            ),
            array(
                "attrid" => "TST_DOCENUM-0123",
                "disable" => array(
                    " ",
                    "0",
                    "1"
                ),
                "expect" => array(
                    "2"
                )
            ),
            array(
                "attrid" => "TST_DOCENUM-ABCD",
                "disable" => array(
                    "b",
                    "d"
                ),
                "expect" => array(
                    "a","b1","b2",
                    "c"
                )
            )
        );
    }

    public function dataDocEnumAddLocale()
    {
        return array(
            array(
                "attrid" => "TST_DOCENUM-0123",
                "key" => 4,
                "label" => "quatro",
                "locale" => array(
                    "en_US" => "four",
                    "fr_FR" => "quatre"
                )
            )
        );
    }
}
