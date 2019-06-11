<?php

namespace Anakeen\Pu\Config;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\FieldAccessManager;
use Anakeen\Core\Utils\Date;

class PuStructBasic extends TestCaseConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_001.struct.xml");
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_002.struct.xml");
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_003.struct.xml");
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_004.struct.xml");
    }

    /**
     * Test Field definition import
     *
     * @dataProvider dataFieldStructure
     *
     * @param string $structureName
     * @param array  $expectedFields
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function testFieldStructure($structureName, array $expectedFields)
    {
        $structure = SEManager::getFamily($structureName);
        $this->assertNotEmpty($structure, "Structure $structureName not found");

        foreach ($expectedFields as $id => $expectedField) {
            $oa = $structure->getAttribute($id);
            $this->assertNotEmpty($oa, "Attribute $id not found");
            $this->assertEquals($expectedField["type"], $oa->type);
            $this->assertEquals($expectedField["label"], $oa->labelText);
            $this->assertEquals($expectedField["access"], FieldAccessManager::getTextAccess($oa->getAccess()));

            foreach ($expectedField["options"] as $kopt => $option) {
                $this->assertEquals($option, $oa->getOption($kopt));
            }
        }
    }

    /**
     *
     * @dataProvider dataFieldDefault
     * @depends      testFieldStructure
     * @param       $structureName
     * @param array $expectedDefaults
     */
    public function testFieldDefault($structureName, array $expectedDefaults)
    {
        $structure = SEManager::getFamily($structureName);
        $this->assertNotEmpty($structure, "Structure $structureName not found");

        $elt = SEManager::createDocument($structure->id);
        foreach ($expectedDefaults as $id => $expectedDefault) {
            $this->assertEquals($expectedDefault, $elt->getRawValue($id));
        }
    }

    /**
     *
     * @dataProvider dataParamInitial
     * @depends      testFieldStructure
     * @param       $structureName
     * @param array $expectedInitials
     */
    public function testParamInitial($structureName, array $expectedInitials)
    {
        $structure = SEManager::getFamily($structureName);
        $this->assertNotEmpty($structure, "Structure $structureName not found");

        foreach ($expectedInitials as $id => $expectedInitial) {
            $this->assertEquals($expectedInitial, $structure->getFamilyParameterValue($id));
        }
    }

    /**
     *
     * @dataProvider dataStructProp
     * @depends      testFieldStructure
     * @param       $structureName
     * @param array $expectedProps
     */
    public function testStructProp($structureName, array $expectedProps)
    {
        $structure = SEManager::getFamily($structureName);
        $this->assertNotEmpty($structure, "Structure $structureName not found");

        foreach ($expectedProps as $id => $expectedProp) {
            switch ($id) {
                case "label":
                    $this->assertEquals($expectedProp, $structure->getTitle());
                    break;
                case "icon":
                    $this->assertEquals($expectedProp, $structure->icon);
                    break;
                case "dfldid":
                    $this->assertEquals($expectedProp, $structure->dfldid);
                    break;
                case "class":
                    $this->assertEquals($expectedProp, $structure->classname);
                    break;
                case "extends":
                    $this->assertEquals($expectedProp, $structure->fromname);
                    break;
                case "schar":
                    $this->assertEquals($expectedProp, $structure->schar);
                    break;
                case "maxrev":
                    $this->assertEquals($expectedProp, $structure->maxrev);
                    break;
                case "tags":
                    foreach ($expectedProp as $tag => $value) {
                        $structure->getATag($tag, $aValue);
                        $this->assertEquals($value, $aValue, "Tag $tag :" . print_r($structure->atags, true));
                    }
                    break;
            }
        }
    }

    protected function writeStructure(SmartElement $structure)
    {
        $t = [];
        foreach ($structure->getAttributes() as $oa) {
            $t[$oa->id] = [
                "type" => $oa->type,
                "label" => $oa->labelText,
                "format" => $oa->format,
                "access" => FieldAccessManager::getTextAccess($oa->getAccess()),
                "options" => $oa->getOptions()
            ];
        }
        var_export($t);
    }

    //region Test Data providers
    public function dataStructProp()
    {
        return [
            [
                "TST_001",
                [
                    "label" => "Test n°1",
                    "icon" => "N1.png",
                    "dfldid" => null,
                    "tags" => [
                        "vendor" => "Anakeen"
                    ]
                ]
            ],
            [
                "TST_002",
                [
                    "label" => "Test n°2",
                    "icon" => "TEST.png",
                    "class" => "Anakeen\Pu\Config\DefaultBehavior",
                    "tags" => [
                        "vendor" => "Anakeen",
                        "Hello" => "World is spheric",
                        "ItsTrue" => true
                    ]
                ]
            ],
            [
                "TST_003",
                [
                    "label" => "Test n°3",
                    "icon" => "N3.png",
                    "dfldid" => null,
                    "extend" => "TST_001",
                    "schar" => "S",
                    "tags" => [
                        "vendor" => null,
                        "testExtended" => "From Test 2"
                    ]
                ]
            ],[
                "TST_004",
                [
                    "label" => "Test n°4",
                    "icon" => null,
                    "dfldid" => null,
                    "extend" => "TST_003",
                    "schar" => null,
                    "maxrev" => 10,
                    "tags" => [
                        "vendor" => null,
                        "testExtended" => "From Test 3"
                    ]
                ]
            ],
        ];
    }

    public function dataFieldDefault()
    {
        return [
            [
                "TST_002",
                [
                    "tst_langue" => "francais",
                    "tst_reprise" => "Non",
                    "tst_date_creation" => Date::getDate(),
                    "tst_vulnerables" => 3,
                    "tst_statut_decision_ca" => 42 + 83 - 5
                ]
            ]
        ];
    }

    public function dataParamInitial()
    {
        return [
            [
                "TST_002",
                [
                    "tst_param_n0" => "0",
                    "tst_param_n1" => "1",
                    "tst_param_n2" => 342 + 30 - 57,
                ]
            ]
        ];
    }

    public function dataFieldStructure()
    {
        $struct01 = array(

            'tst_f_title' =>
                array(
                    'type' => 'frame',
                    'label' => 'Titre',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Le titre',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_t_tab' =>
                array(
                    'type' => 'tab',
                    'label' => 'Basiques',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fr_rels' =>
                array(
                    'type' => 'frame',
                    'label' => 'Relations',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_account' =>
                array(
                    'type' => 'account',
                    'label' => 'Un compte',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_account_multiple' =>
                array(
                    'type' => 'account',
                    'label' => 'Des comptes',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'multiple' => 'yes',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_docid' =>
                array(
                    'type' => 'docid',
                    'label' => 'Un document',
                    'format' => 'TST_001',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_docid_multiple' =>
                array(
                    'type' => 'docid',
                    'label' => 'Des documents',
                    'format' => 'TST_001',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'multiple' => 'yes',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fr_date' =>
                array(
                    'type' => 'frame',
                    'label' => 'Le temps',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_date' =>
                array(
                    'type' => 'date',
                    'label' => 'Une date',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_time' =>
                array(
                    'type' => 'time',
                    'label' => 'Une heure',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_timestamp' =>
                array(
                    'type' => 'timestamp',
                    'label' => 'Une date avec  une heure',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fr_number' =>
                array(
                    'type' => 'frame',
                    'label' => 'Les nombres',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_integer' =>
                array(
                    'type' => 'int',
                    'label' => 'Un entier',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_double' =>
                array(
                    'type' => 'double',
                    'label' => 'Un décimal',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_money' =>
                array(
                    'type' => 'money',
                    'label' => 'Un sous',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fr_misc' =>
                array(
                    'type' => 'frame',
                    'label' => 'Divers',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_password' =>
                array(
                    'type' => 'password',
                    'label' => 'Un mot de passe',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_color' =>
                array(
                    'type' => 'color',
                    'label' => 'Une couleur',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fr_file' =>
                array(
                    'type' => 'frame',
                    'label' => 'Fichiers & images',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_file' =>
                array(
                    'type' => 'file',
                    'label' => 'Un fichier',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_image' =>
                array(
                    'type' => 'image',
                    'label' => 'Une image',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fr_text' =>
                array(
                    'type' => 'frame',
                    'label' => 'Les textes',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_htmltext' =>
                array(
                    'type' => 'htmltext',
                    'label' => 'Un texte formaté',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_longtext' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Un texte multiligne',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_text' =>
                array(
                    'type' => 'text',
                    'label' => 'Un texte simple',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fr_enumsimple' =>
                array(
                    'type' => 'frame',
                    'label' => 'Énumérés directs simple',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_enumlist' =>
                array(
                    'type' => 'enum',
                    'label' => 'Un énuméré liste',
                    'format' => 'TST_001-countries',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_enumhorizontal' =>
                array(
                    'type' => 'enum',
                    'label' => 'Un énuméré multi-niveau',
                    'format' => 'TST_001-colors',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fr_enummultiple' =>
                array(
                    'type' => 'frame',
                    'label' => 'Énumérés directs multiple',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_enumslist' =>
                array(
                    'type' => 'enum',
                    'label' => 'Des énumérés liste',
                    'format' => 'TST_001-countries',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'multiple' => 'yes',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_enumshorizontal' =>
                array(
                    'type' => 'enum',
                    'label' => 'Des énumérés multi-niveau',
                    'format' => 'TST_001-colors',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'multiple' => 'yes',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_t_tab_date' =>
                array(
                    'type' => 'tab',
                    'label' => 'Les dates',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_frame_date' =>
                array(
                    'type' => 'frame',
                    'label' => 'Date, heures & date avec l\'heure',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_array_dates' =>
                array(
                    'type' => 'array',
                    'label' => 'Le temps',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_date_array' =>
                array(
                    'type' => 'date',
                    'label' => 'Des dates',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_time_array' =>
                array(
                    'type' => 'time',
                    'label' => 'Des heures',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_timestamp_array' =>
                array(
                    'type' => 'timestamp',
                    'label' => 'Des dates avec l\'heure',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_t_tab_relations' =>
                array(
                    'type' => 'tab',
                    'label' => 'Les relations',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_frame_relation' =>
                array(
                    'type' => 'frame',
                    'label' => 'Relations à entretenir',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_array_docid' =>
                array(
                    'type' => 'array',
                    'label' => 'Les documents',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_docid_array' =>
                array(
                    'type' => 'docid',
                    'label' => 'Des documents',
                    'format' => 'TST_001',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_docid_multiple_array' =>
                array(
                    'type' => 'docid',
                    'label' => 'Encore plus de documents',
                    'format' => 'TST_001',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'multiple' => 'yes',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_array_account' =>
                array(
                    'type' => 'array',
                    'label' => 'Les comptes',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_account_array' =>
                array(
                    'type' => 'account',
                    'label' => 'Des comptes',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_account_multiple_array' =>
                array(
                    'type' => 'account',
                    'label' => 'Encore plus de comptes',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'multiple' => 'yes',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_t_tab_numbers' =>
                array(
                    'type' => 'tab',
                    'label' => 'Les nombres',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_frame_numbers' =>
                array(
                    'type' => 'frame',
                    'label' => 'Entier, décimaux et monnaie',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_array_numbers' =>
                array(
                    'type' => 'array',
                    'label' => 'Quelques nombres',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_double_array' =>
                array(
                    'type' => 'double',
                    'label' => 'Des décimaux',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_integer_array' =>
                array(
                    'type' => 'int',
                    'label' => 'Des entiers',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_money_array' =>
                array(
                    'type' => 'money',
                    'label' => 'Des sous',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_t_tab_misc' =>
                array(
                    'type' => 'tab',
                    'label' => 'Divers',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_frame_misc' =>
                array(
                    'type' => 'frame',
                    'label' => 'Énuméré, couleur et mot de passe',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_array_misc' =>
                array(
                    'type' => 'array',
                    'label' => 'Quelques diverses données',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_color_array' =>
                array(
                    'type' => 'color',
                    'label' => 'Des couleurs',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_password_array' =>
                array(
                    'type' => 'password',
                    'label' => 'Des mots de passe',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_t_tab_files' =>
                array(
                    'type' => 'tab',
                    'label' => 'Les fichiers',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_frame_files' =>
                array(
                    'type' => 'frame',
                    'label' => 'Fichiers & images',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_array_files' =>
                array(
                    'type' => 'array',
                    'label' => 'Quelques fichiers',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_file_array' =>
                array(
                    'type' => 'file',
                    'label' => 'Des fichiers',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_image_array' =>
                array(
                    'type' => 'image',
                    'label' => 'Des images',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_t_tab_texts' =>
                array(
                    'type' => 'tab',
                    'label' => 'Les textes',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_frame_texts' =>
                array(
                    'type' => 'frame',
                    'label' => 'Les textes non formatés',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_array_texts' =>
                array(
                    'type' => 'array',
                    'label' => 'Textes simples et multilignes',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_text_array' =>
                array(
                    'type' => 'text',
                    'label' => 'Des textes',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_longtext_array' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Des textes multiligne',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_array_html' =>
                array(
                    'type' => 'array',
                    'label' => 'Les textes HTML',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_htmltext_array' =>
                array(
                    'type' => 'htmltext',
                    'label' => 'Des textes formatés',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
        );

        $struct02 = array(

            'tst_notification' =>
                array(
                    'type' => 'frame',
                    'label' => '',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_titre' =>
                array(
                    'type' => 'text',
                    'label' => 'Titre',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_notification_montants' =>
                array(
                    'type' => 'text',
                    'label' => 'Notification',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'viewtemplate' => 'TEST:csl_notification_montants.xml',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_notification_statut' =>
                array(
                    'type' => 'enum',
                    'label' => 'Statut',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_notification_statut',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'viewtemplate' => 'TEST:csl_notification_statut.xml',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_valide_rs_2' =>
                array(
                    'type' => 'enum',
                    'label' => 'Validé RS 2',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_statut_valide_rs_2',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'hcheck',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_identification_tab' =>
                array(
                    'type' => 'tab',
                    'label' => 'Identification',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_identification_frm' =>
                array(
                    'type' => 'frame',
                    'label' => 'Identification',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_type_soutien' =>
                array(
                    'type' => 'enum',
                    'label' => 'Type de soutien',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_type_soutien',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'eformat' => 'auto',
                            'viewtemplate' => 'TEST:csl_type_soutien.xml',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_territorialite_don' =>
                array(
                    'type' => 'enum',
                    'label' => 'Territorialité du don',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_territorialite_don',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'multiple' => 'yes',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_reprise' =>
                array(
                    'type' => 'enum',
                    'label' => 'Reprise',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_reprise',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'bmenu' => 'no',
                            'eformat' => 'hcheck',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_num_action' =>
                array(
                    'type' => 'text',
                    'label' => 'Numéro action',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_ancien_num_projet' =>
                array(
                    'type' => 'text',
                    'label' => 'Ancien n° projet',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_phase_prec' =>
                array(
                    'type' => 'docid',
                    'label' => 'Phase précédente',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'searchcriteria' => 'hidden',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_phase_prec_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Phase précédente (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_phase_prec',
                        ),
                ),
            'tst_phase_suiv' =>
                array(
                    'type' => 'docid',
                    'label' => 'Phase suivante',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'searchcriteria' => 'hidden',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_phase_suiv_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Phase suivante (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_phase_suiv',
                        ),
                ),
            'tst_action_cofinancee' =>
                array(
                    'type' => 'enum',
                    'label' => 'Action gérée par le service AFI',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_action_cofinancee',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'hcheck',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_cofinancements' =>
                array(
                    'type' => 'docid',
                    'label' => 'Cofinancements',
                    'format' => 'TEST_GEP_COFINANCEMENT',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_cofinancements_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Cofinancements (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_cofinancements',
                        ),
                ),
            'tst_type_action_soutenue' =>
                array(
                    'type' => 'docid',
                    'label' => 'Type d\'action soutenue',
                    'format' => 'TEST_GEP_TYPE_ACTION_SOUTENUE',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_type_action_soutenue_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Type d\'action soutenue (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_type_action_soutenue',
                        ),
                ),
            'tst_code_cpt_anal_domaine' =>
                array(
                    'type' => 'text',
                    'label' => 'Code comptable analytique domaine',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_zone_geo' =>
                array(
                    'type' => 'docid',
                    'label' => 'Zone géographique',
                    'format' => 'TEST_GEP_ZONE_GEOGRAPHIQUE',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'sortable' => 'asc',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_zone_geo_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Zone géographique (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_zone_geo',
                        ),
                ),
            'tst_description_spa' =>
                array(
                    'type' => 'docid',
                    'label' => 'Stratégie & plans d\'action',
                    'format' => 'TEST_GEP_STRATEGIE_PLAN_ACTION',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_description_spa_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Stratégie & plans d\'action (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_description_spa',
                        ),
                ),
            'tst_zone_geo_search' =>
                array(
                    'type' => 'text',
                    'label' => 'Zone géographique pour recherche',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_code_cpt_anal_geo' =>
                array(
                    'type' => 'text',
                    'label' => 'Code comptable analytique géographique',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_service_geo' =>
                array(
                    'type' => 'account',
                    'label' => 'Service géographique',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'sortable' => 'asc',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_service_geo_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Service géographique (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_service_geo',
                        ),
                ),
            'tst_code_cpt_anal_fonc' =>
                array(
                    'type' => 'text',
                    'label' => 'Code comptable analytique fonctionnel',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_region_pador' =>
                array(
                    'type' => 'enum',
                    'label' => 'Région PADOR',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_region_pador',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_localisation' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Localisation',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_alerte_acteur' =>
                array(
                    'type' => 'text',
                    'label' => 'Notification',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'viewtemplate' => 'TEST:csl_alerte_acteur.xml',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_acteur_principal' =>
                array(
                    'type' => 'docid',
                    'label' => 'Acteur principal',
                    'format' => 'TEST_GEP_PERSONNE_MORALE',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'sortable' => 'asc',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_acteur_principal_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Acteur principal (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_acteur_principal',
                        ),
                ),
            'tst_autres_acteurs' =>
                array(
                    'type' => 'docid',
                    'label' => 'Autres acteurs concernés',
                    'format' => 'TEST_GEP_PERSONNE_MORALE',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'multiple' => 'yes',
                            'doctitle' => 'auto',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_autres_acteurs_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Autres acteurs concernés (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_autres_acteurs',
                        ),
                ),
            'tst_secteur_cidse' =>
                array(
                    'type' => 'enum',
                    'label' => 'Secteur CIDSE',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_secteur_cidse',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_secteur_pador' =>
                array(
                    'type' => 'enum',
                    'label' => 'Secteur d\'activité PADOR',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_secteur_pador',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_theme_ro_princ' =>
                array(
                    'type' => 'docid',
                    'label' => 'Thématique RO principale',
                    'format' => 'TEST_GEP_STRATEGIE_PARTENARIALE',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'sortable' => 'asc',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_theme_ro_princ_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Thématique RO principale (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_theme_ro_princ',
                        ),
                ),
            'tst_theme_ro_sec' =>
                array(
                    'type' => 'docid',
                    'label' => 'Thématique RO secondaire',
                    'format' => 'TEST_GEP_STRATEGIE_PARTENARIALE',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'multiple' => 'yes',
                            'doctitle' => 'auto',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_theme_ro_sec_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Thématique RO secondaire (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_theme_ro_sec',
                        ),
                ),
            'tst_intitule_action' =>
                array(
                    'type' => 'text',
                    'label' => 'Intitulé action',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_traduction_fr' =>
                array(
                    'type' => 'text',
                    'label' => 'Traduction en français (si nécessaire)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_langue' =>
                array(
                    'type' => 'enum',
                    'label' => 'Langue',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_langue',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'eformat' => 'auto',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_date_creation' =>
                array(
                    'type' => 'date',
                    'label' => 'Date de création',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_date_presentation_cpf' =>
                array(
                    'type' => 'date',
                    'label' => 'Date CTP du service',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'sortable' => 'asc',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_createur' =>
                array(
                    'type' => 'account',
                    'label' => 'Créateur',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_createur_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Créateur (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_createur',
                        ),
                ),
            'tst_confidentiel' =>
                array(
                    'type' => 'enum',
                    'label' => 'Confidentiel',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_confidentiel',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'eformat' => 'bool',
                            'system' => 'yes',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_description_tab' =>
                array(
                    'type' => 'tab',
                    'label' => 'Description de l\'action',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_description_frm' =>
                array(
                    'type' => 'frame',
                    'label' => 'Description de l\'action',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_groupe_cible' =>
                array(
                    'type' => 'enum',
                    'label' => 'Groupe cible',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_groupe_cible',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'multiple' => 'yes',
                            'eformat' => 'auto',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_vulnerables' =>
                array(
                    'type' => 'enum',
                    'label' => 'Vulnérables',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_vulnerables',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'hcheck',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_comm_groupe_vulnerables' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Commentaires Groupes Vulnérables',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_risque' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Risque',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_notes_repris' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Notes et documents repris',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_notes_array' =>
                array(
                    'type' => 'array',
                    'label' => 'Note et documents',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_notes' =>
                array(
                    'type' => 'file',
                    'label' => 'Fichiers',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_dossier_projet_array' =>
                array(
                    'type' => 'array',
                    'label' => 'Dossier projet',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_dossier_projet' =>
                array(
                    'type' => 'file',
                    'label' => 'Fichiers',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_position_episcopat' =>
                array(
                    'type' => 'enum',
                    'label' => 'Position de l\'Episcopat local',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_position_episcopat',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'eformat' => 'auto',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_relation_eglise' =>
                array(
                    'type' => 'enum',
                    'label' => 'Relation avec l\'Eglise',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_relation_eglise',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'eformat' => 'auto',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_commentare_relation_eglise' =>
                array(
                    'type' => 'text',
                    'label' => 'Commentaires sur la relation avec l\'Eglise',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_typologie_action' =>
                array(
                    'type' => 'enum',
                    'label' => 'Typologie de l\'action',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_typologie_action',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'eformat' => 'auto',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_contexte' =>
                array(
                    'type' => 'htmltext',
                    'label' => 'Contexte de l\'action',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'toolbar' => 'Basic',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_benef' =>
                array(
                    'type' => 'htmltext',
                    'label' => 'Bénéficiaires',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'toolbar' => 'Basic',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_nb_benef_directs' =>
                array(
                    'type' => 'int',
                    'label' => 'Nombre de bénéficiaires directs',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_nb_benef_indirects' =>
                array(
                    'type' => 'int',
                    'label' => 'Nombre de bénéficiaires indirects',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_objectifs' =>
                array(
                    'type' => 'htmltext',
                    'label' => 'Objectifs',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'toolbar' => 'Basic',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_activites' =>
                array(
                    'type' => 'htmltext',
                    'label' => 'Activités',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'toolbar' => 'Basic',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_resultats_attendus' =>
                array(
                    'type' => 'htmltext',
                    'label' => 'Résultats attendus',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'toolbar' => 'Basic',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_moyens' =>
                array(
                    'type' => 'htmltext',
                    'label' => 'Moyens',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'toolbar' => 'Basic',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_histoire' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Histoire',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_init_nouvelle' =>
                array(
                    'type' => 'enum',
                    'label' => 'Initiative nouvelle',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_init_nouvelle',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'hcheck',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_init_pluriannu' =>
                array(
                    'type' => 'enum',
                    'label' => 'Initiative pluriannuelle',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_init_pluriannu',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'hcheck',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_duree_prev_action' =>
                array(
                    'type' => 'int',
                    'label' => 'Durée prévue de l\'action (mois)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_rapport_soutien_prec_date' =>
                array(
                    'type' => 'date',
                    'label' => 'Rapport du soutien fourni le',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_rapport_soutien_prec_array' =>
                array(
                    'type' => 'array',
                    'label' => 'Rapports du soutien - fichiers',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_rapport_soutien_prec_fichier' =>
                array(
                    'type' => 'file',
                    'label' => 'Rapport du soutien',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_amplitude_action' =>
                array(
                    'type' => 'enum',
                    'label' => 'Amplitude de l\'action',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_amplitude_action',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'eformat' => 'auto',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_date_debut_action' =>
                array(
                    'type' => 'date',
                    'label' => 'Date de début de l\'action',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_date_fin_action' =>
                array(
                    'type' => 'date',
                    'label' => 'Date de fin de l\'action',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_resume' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Résumé (5 l. maxi pour CA)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_date_prochain_ca' =>
                array(
                    'type' => 'date',
                    'label' => 'Prochaine instruction complète',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_financement_tab' =>
                array(
                    'type' => 'tab',
                    'label' => 'Statut & Financement',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_financement_frm' =>
                array(
                    'type' => 'frame',
                    'label' => 'Financement',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_budget_action' =>
                array(
                    'type' => 'array',
                    'label' => 'Budget de l\'action',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_budget_type_depense' =>
                array(
                    'type' => 'text',
                    'label' => 'Type de dépense',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_budget_montant' =>
                array(
                    'type' => 'money',
                    'label' => 'Montant',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fin_total' =>
                array(
                    'type' => 'money',
                    'label' => 'Total',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fin_montant_demande' =>
                array(
                    'type' => 'money',
                    'label' => 'Montant demandé par le partenaire',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fin_montant_propose' =>
                array(
                    'type' => 'money',
                    'label' => 'Montant proposé par le CM',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'elabel' => 'ne doit pas être modifié après le CA',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fin_montant_a_payer_cm' =>
                array(
                    'type' => 'money',
                    'label' => 'Montant à payer CM',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'elabel' => 'à renseigner pour mise en paiement',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_fin_commentaire_cm' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Commentaires CM',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_budget_montant_an_2' =>
                array(
                    'type' => 'money',
                    'label' => 'Estimation budget total année 2',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_budget_estimation_ccfd_an_2' =>
                array(
                    'type' => 'money',
                    'label' => 'Estimation contribution TEST année 2',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_budget_montant_an_3' =>
                array(
                    'type' => 'money',
                    'label' => 'Estimation budget total année 3',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_budget_estimation_ccfd_an_3' =>
                array(
                    'type' => 'money',
                    'label' => 'Estimation contribution TEST année 3',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_plan_financement' =>
                array(
                    'type' => 'array',
                    'label' => 'Plan de financement',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_plan_fin_lib_bailleur' =>
                array(
                    'type' => 'text',
                    'label' => 'Libellé bailleur',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_plan_fin_bailleur' =>
                array(
                    'type' => 'docid',
                    'label' => 'Bailleur',
                    'format' => 'TEST_GEP_PERSONNE_MORALE',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_plan_fin_montant' =>
                array(
                    'type' => 'money',
                    'label' => 'Montant',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_plan_fin_total' =>
                array(
                    'type' => 'money',
                    'label' => 'Total plan de financement',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_plan_fin_annee' =>
                array(
                    'type' => 'int',
                    'label' => 'Année ?',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_frm' =>
                array(
                    'type' => 'frame',
                    'label' => 'Statut dans les process internes',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_valide_rs' =>
                array(
                    'type' => 'enum',
                    'label' => 'Validé RS',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_statut_valide_rs',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'hcheck',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_bon_a_payer' =>
                array(
                    'type' => 'enum',
                    'label' => 'Bon à Payer',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_statut_bon_a_payer',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'hcheck',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_montant_bap' =>
                array(
                    'type' => 'money',
                    'label' => 'Montant bon à payer',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_date_ca_2' =>
                array(
                    'type' => 'date',
                    'label' => 'Date Bon à payer',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_demande_maj_directeur' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Demande de modification',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_commentaire_directeur' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Commentaire validation Directeur',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_valide_directeur' =>
                array(
                    'type' => 'enum',
                    'label' => 'Validé directeur',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_statut_valide_directeur',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'hcheck',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_raison_abandon_createur' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Raison d\'abandon par le créateur',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_raison_abandon_ca' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Raison d\'abandon par le CA',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_date_pres_cmp' =>
                array(
                    'type' => 'date',
                    'label' => 'Date présentation à la CTP',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_decision_cmp' =>
                array(
                    'type' => 'enum',
                    'label' => 'Décision CTP',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_statut_decision_cmp',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'auto',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_montant_decide_cmp' =>
                array(
                    'type' => 'money',
                    'label' => 'Montant CTP',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_commentaire_cmp' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Commentaire CTP',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_montant_tranche_tb' =>
                array(
                    'type' => 'array',
                    'label' => 'Montant par tranche',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_date_valeur_banque' =>
                array(
                    'type' => 'date',
                    'label' => 'Date de valeur banque',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_num_enregistrement' =>
                array(
                    'type' => 'text',
                    'label' => 'Numéro d\'enregistrement',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_montant_tranche' =>
                array(
                    'type' => 'money',
                    'label' => 'Montant tranche',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_beneficiaire_tranche' =>
                array(
                    'type' => 'text',
                    'label' => 'Bénéficiaire',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_contrat_pdf' =>
                array(
                    'type' => 'file',
                    'label' => 'Contrat PDF',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_contrat_pdf_ar' =>
                array(
                    'type' => 'file',
                    'label' => 'Contrat PDF (AR)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_type_reglement' =>
                array(
                    'type' => 'enum',
                    'label' => 'Type de règlement',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_statut_type_reglement',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'auto',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_contrat_signe' =>
                array(
                    'type' => 'file',
                    'label' => 'Contrat ou convention signé',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_dossier_compta' =>
                array(
                    'type' => 'file',
                    'label' => 'Dossier compta (Transfert)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_dossier_compta_ar' =>
                array(
                    'type' => 'file',
                    'label' => 'Dossier compta (AR)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_dossier_compta_demande_regl' =>
                array(
                    'type' => 'file',
                    'label' => 'Dossier compta (Demande R.)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_date_reception_ar' =>
                array(
                    'type' => 'date',
                    'label' => 'Date réception AR',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_ar' =>
                array(
                    'type' => 'file',
                    'label' => 'Accusé de réception',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_unused_attr_array' =>
                array(
                    'type' => 'array',
                    'label' => 'Ancien champ',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_date_tranche' =>
                array(
                    'type' => 'date',
                    'label' => 'Ancienne Date tranche (unused)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'searchcriteria' => 'hidden',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_num_ordre_tranche' =>
                array(
                    'type' => 'text',
                    'label' => 'Ancien numéro (unused)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'searchcriteria' => 'hidden',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_beneficiaire_tranche' =>
                array(
                    'type' => 'text',
                    'label' => 'Ancien bénéficiaire (unused)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'searchcriteria' => 'hidden',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_ancien_process_frm' =>
                array(
                    'type' => 'frame',
                    'label' => 'Ancien process',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_avis_cpf' =>
                array(
                    'type' => 'enum',
                    'label' => 'Avis ancienne CMP',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_statut_avis_cpf',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'auto',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_commentaire_cpf' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Commentaire ancienne CMP',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_montant_propose_cpf' =>
                array(
                    'type' => 'money',
                    'label' => 'Montant proposé par l\'ancienne CMP',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_date_pres_ca' =>
                array(
                    'type' => 'date',
                    'label' => 'Date présentation à la CA',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_decision_ca' =>
                array(
                    'type' => 'enum',
                    'label' => 'Décision CA',
                    'format' => 'TEST_GEP_SOUTIEN_CIRLONG-csl_statut_decision_ca',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'system' => 'yes',
                            'eformat' => 'auto',
                            'bmenu' => 'no',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_montant_decide_ca' =>
                array(
                    'type' => 'money',
                    'label' => 'Montant CA',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_statut_commentaire_ca' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Commentaire CA',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_eval_tab' =>
                array(
                    'type' => 'tab',
                    'label' => 'Evaluation',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_eval_frm' =>
                array(
                    'type' => 'frame',
                    'label' => 'Evaluation',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_eval_rapports' =>
                array(
                    'type' => 'array',
                    'label' => 'Rapports',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_eval_rapport' =>
                array(
                    'type' => 'file',
                    'label' => 'Rapport',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_eval_results' =>
                array(
                    'type' => 'array',
                    'label' => 'Résultats',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_eval_indic_1' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Résultat',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_eval_indic_2' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Indicateur 1',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_eval_indic_3' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Indicateur 2',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_eval_eval' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Evaluation',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_eval_impact' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Impact',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_info_tech_tab' =>
                array(
                    'type' => 'tab',
                    'label' => 'Informations Techniques',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_info_tech' =>
                array(
                    'type' => 'frame',
                    'label' => 'Informations Techniques',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_annee' =>
                array(
                    'type' => 'text',
                    'label' => 'Année',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_dern_modif_acteur' =>
                array(
                    'type' => 'int',
                    'label' => 'Date de dernière modification de l\'acteur',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_acteur_principal_old' =>
                array(
                    'type' => 'text',
                    'label' => 'Ancien acteur principal',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_groupe_compta' =>
                array(
                    'type' => 'account',
                    'label' => 'Groupe Compa',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_groupe_compta_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Groupe Compa (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_groupe_compta',
                        ),
                ),
            'tst_charge_partenariat' =>
                array(
                    'type' => 'account',
                    'label' => 'Chargé de partenariat international',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'multiple' => 'yes',
                            'doctitle' => 'auto',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_charge_partenariat_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Chargé de partenariat international (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_charge_partenariat',
                        ),
                ),
            'tst_cpt_zg' =>
                array(
                    'type' => 'int',
                    'label' => 'Numéro lié à a la zone géographique',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_cpt_csl' =>
                array(
                    'type' => 'int',
                    'label' => 'Numéro lié à l\'année',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_rs_dpi_szg' =>
                array(
                    'type' => 'account',
                    'label' => 'RS DPI du service choisi',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_rs_dpi_szg_title' =>
                array(
                    'type' => 'text',
                    'label' => 'RS DPI du service choisi (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_rs_dpi_szg',
                        ),
                ),
            'tst_assistant_dpi_szg' =>
                array(
                    'type' => 'account',
                    'label' => 'Assistant DPI du service choisi',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_assistant_dpi_szg_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Assistant DPI du service choisi (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_assistant_dpi_szg',
                        ),
                ),
            'tst_cm_dpi_szg' =>
                array(
                    'type' => 'account',
                    'label' => 'CM DPI du service choisi',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_cm_dpi_szg_title' =>
                array(
                    'type' => 'text',
                    'label' => 'CM DPI du service choisi (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_cm_dpi_szg',
                        ),
                ),
            'tst_groupe_dir_dpi' =>
                array(
                    'type' => 'account',
                    'label' => 'Groupe Directeur DPI',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_groupe_dir_dpi_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Groupe Directeur DPI (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_groupe_dir_dpi',
                        ),
                ),
            'tst_groupe_decis_cpf' =>
                array(
                    'type' => 'account',
                    'label' => 'Groupe Décisionnaire CTP',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_groupe_decis_cpf_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Groupe Décisionnaire CTP (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_groupe_decis_cpf',
                        ),
                ),
            'tst_groupe_decis_ca' =>
                array(
                    'type' => 'account',
                    'label' => 'Groupe Décisionnaire CA',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_groupe_decis_ca_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Groupe Décisionnaire CA (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_groupe_decis_ca',
                        ),
                ),
            'tst_groupe_autir_44b' =>
                array(
                    'type' => 'account',
                    'label' => 'Groupe autorisé E2 - E3',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_groupe_autir_44b_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Groupe autorisé E2 - E3 (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_groupe_autir_44b',
                        ),
                ),
            'tst_groupe_autir_24b' =>
                array(
                    'type' => 'account',
                    'label' => 'Groupe autorisé E2 - E4bis',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_groupe_autir_24b_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Groupe autorisé E2 - E4bis (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_groupe_autir_24b',
                        ),
                ),
            'tst_groupe_autir_21' =>
                array(
                    'type' => 'account',
                    'label' => 'Groupe autorisé E2 - E1',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_groupe_autir_21_title' =>
                array(
                    'type' => 'text',
                    'label' => 'Groupe autorisé E2 - E1 (titre)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'autotitle' => 'yes',
                            'relativeOrder' => 'tst_groupe_autir_21',
                        ),
                ),
            'tst_info_mod' =>
                array(
                    'type' => 'frame',
                    'label' => 'Modeles de documents',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_tpl_ar_vierge' =>
                array(
                    'type' => 'file',
                    'label' => 'Modèle AR vierge',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_tpl_demande_r' =>
                array(
                    'type' => 'file',
                    'label' => 'Modèle Demande de règlement',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_tpl_transfert' =>
                array(
                    'type' => 'file',
                    'label' => 'Modèles transfert de fond',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_tpl_contrat' =>
                array(
                    'type' => 'file',
                    'label' => 'Contrat',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_tpl_ar' =>
                array(
                    'type' => 'file',
                    'label' => 'AR',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_tpl_fiche_cpf' =>
                array(
                    'type' => 'file',
                    'label' => 'Fiche CPF',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'cls_flags' =>
                array(
                    'type' => 'frame',
                    'label' => 'Flags',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_in_copy' =>
                array(
                    'type' => 'int',
                    'label' => 'Flag de copie',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_param' =>
                array(
                    'type' => 'frame',
                    'label' => 'Modeles de documents',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_ar_vierge' =>
                array(
                    'type' => 'docid',
                    'label' => 'Modèle AR vierge',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_demande_r' =>
                array(
                    'type' => 'docid',
                    'label' => 'Modèle Demande de règlement',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_transfert_fr' =>
                array(
                    'type' => 'docid',
                    'label' => 'Modèles transfert de fond (francais)',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_transfert_en' =>
                array(
                    'type' => 'docid',
                    'label' => 'Modèles transfert de fond (anglais)',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_transfert_po' =>
                array(
                    'type' => 'docid',
                    'label' => 'Modèles transfert de fond (portugais)',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_transfert_es' =>
                array(
                    'type' => 'docid',
                    'label' => 'Modèles transfert de fond (espagnol)',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_contrat_fr' =>
                array(
                    'type' => 'docid',
                    'label' => 'Modèles contrat (francais)',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_contrat_en' =>
                array(
                    'type' => 'docid',
                    'label' => 'Modèles contrat (anglais)',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_contrat_po' =>
                array(
                    'type' => 'docid',
                    'label' => 'Modèles contrat (portugais)',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_contrat_es' =>
                array(
                    'type' => 'docid',
                    'label' => 'Modèles contrat (espagnol)',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_header_liasse' =>
                array(
                    'type' => 'docid',
                    'label' => 'Page de garde pour les liasses',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_zonegeo_liasse' =>
                array(
                    'type' => 'docid',
                    'label' => 'Page de section zone géographique pour les liasses',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_ar' =>
                array(
                    'type' => 'docid',
                    'label' => 'AR',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_fiche_cpf' =>
                array(
                    'type' => 'docid',
                    'label' => 'Fiche CPF',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_nb_jour_control_acteur' =>
                array(
                    'type' => 'int',
                    'label' => 'Nombre de jours avant notification d\'un acteur non modifié',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mt_envoyer_rs' =>
                array(
                    'type' => 'docid',
                    'label' => 'Alerte à envoyer au rs',
                    'format' => 'MAILTEMPLATE',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mt_validation_rs' =>
                array(
                    'type' => 'docid',
                    'label' => 'Alerte validation RS - directeur dpi',
                    'format' => 'MAILTEMPLATE',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mt_validation_bap_vers_autres' =>
                array(
                    'type' => 'docid',
                    'label' => 'Alerte validation BAP - vers autres destinataires',
                    'format' => 'MAILTEMPLATE',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_grp_autres_dest_bap' =>
                array(
                    'type' => 'account',
                    'label' => 'Autre Groupe Destinataire BAP (cm dpi cofinancement, ...)',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'doctitle' => 'auto',
                            'match' => 'group',
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_fichier_concat' =>
                array(
                    'type' => 'docid',
                    'label' => 'Fichier à concaténer (traitement par lot)',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
            'tst_mod_fichier_init' =>
                array(
                    'type' => 'docid',
                    'label' => 'Fichier d\'initialisation (traitement par lot)',
                    'format' => 'TEST_MODELES',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
        );


        $struct03only = [
            'tst_f_n3' =>
                array(
                    'type' => 'frame',
                    'label' => 'Level 3',
                    'format' => null,
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::first',
                        ),
                ),
            'tst_three' =>
                array(
                    'type' => 'text',
                    'label' => 'Three',
                    'format' => '',
                    'access' => 'Read',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                )
        ];

        // Structure extends
        $struct03 = array_merge($struct01, $struct03only);
        // Field override
        $struct03["tst_account"]["access"] = "None";

        return [
            [
                "TST_001",
                $struct01
            ],
            [
                "TST_002",
                $struct02
            ],
            [
                "TST_003",
                $struct03
            ],
        ];
    }
    //endregion
}
