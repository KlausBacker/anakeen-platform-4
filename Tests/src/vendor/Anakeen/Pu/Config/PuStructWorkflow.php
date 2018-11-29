<?php

namespace Anakeen\Pu\Config;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\FieldAccessManager;

class PuStructWorkflow extends TestCaseConfig
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_I001.struct.xml");
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_W001.struct.xml");
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

        //$this->writeStructure($structure);
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
            'tst_desc' =>
                array(
                    'type' => 'longtext',
                    'label' => 'Desription',
                    'format' => '',
                    'access' => 'ReadWrite',
                    'options' =>
                        array(
                            'relativeOrder' => '::auto',
                        ),
                ),
        );


        $structW01 = array(
                'fr_basic' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Basique',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'ba_title' =>
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
                'wf_desc' =>
                    array(
                        'type' => 'longtext',
                        'label' => 'description',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wf_famid' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Structure',
                        'format' => '-1',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wf_fam' =>
                    array(
                        'type' => 'text',
                        'label' => 'Structure (titre)',
                        'format' => '',
                        'access' => 'Read',
                        'options' =>
                            array(
                                'elabel' => 'Structure compatible avec ce cycle',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'dpdoc_fr_dyn' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Profil dynamique',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'dpdoc_famid' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Structure',
                        'format' => '-1',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'dpdoc_fam' =>
                    array(
                        'type' => 'text',
                        'label' => 'Structure (titre)',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'elabel' => 'Structure utilisée pour le profil dynamique',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wf_tab_states' =>
                    array(
                        'type' => 'tab',
                        'label' => 'Étapes',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fre_tst_sl_redaction' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Paramètre de l\'étape e_tst_sl_redaction',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_ide_tst_sl_redaction' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Profil e_tst_sl_redaction',
                        'format' => 'PROFIL',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fallide_tst_sl_redaction' =>
                    array(
                        'type' => 'docid',
                        'label' => 'e_tst_sl_redaction field access list',
                        'format' => 'FIELDACCESSLAYERLIST',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mskide_tst_sl_redaction' =>
                    array(
                        'type' => 'docid',
                        'label' => 'masque e_tst_sl_redaction',
                        'format' => 'MASK',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_colore_tst_sl_redaction' =>
                    array(
                        'type' => 'color',
                        'label' => 'couleur e_tst_sl_redaction',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_cvide_tst_sl_redaction' =>
                    array(
                        'type' => 'docid',
                        'label' => 'contrôle de vue e_tst_sl_redaction',
                        'format' => 'CVDOC',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mtide_tst_sl_redaction' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel e_tst_sl_redaction',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_tmide_tst_sl_redaction' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur e_tst_sl_redaction',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fre_tst_sl_validee_rs' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Paramètre de l\'étape e_tst_sl_validee_rs',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_ide_tst_sl_validee_rs' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Profil e_tst_sl_validee_rs',
                        'format' => 'PROFIL',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fallide_tst_sl_validee_rs' =>
                    array(
                        'type' => 'docid',
                        'label' => 'e_tst_sl_validee_rs field access list',
                        'format' => 'FIELDACCESSLAYERLIST',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mskide_tst_sl_validee_rs' =>
                    array(
                        'type' => 'docid',
                        'label' => 'masque e_tst_sl_validee_rs',
                        'format' => 'MASK',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_colore_tst_sl_validee_rs' =>
                    array(
                        'type' => 'color',
                        'label' => 'couleur e_tst_sl_validee_rs',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_cvide_tst_sl_validee_rs' =>
                    array(
                        'type' => 'docid',
                        'label' => 'contrôle de vue e_tst_sl_validee_rs',
                        'format' => 'CVDOC',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mtide_tst_sl_validee_rs' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel e_tst_sl_validee_rs',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_tmide_tst_sl_validee_rs' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur e_tst_sl_validee_rs',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fre_tst_sl_archivee' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Paramètre de l\'étape e_tst_sl_archivee',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_ide_tst_sl_archivee' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Profil e_tst_sl_archivee',
                        'format' => 'PROFIL',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fallide_tst_sl_archivee' =>
                    array(
                        'type' => 'docid',
                        'label' => 'e_tst_sl_archivee field access list',
                        'format' => 'FIELDACCESSLAYERLIST',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mskide_tst_sl_archivee' =>
                    array(
                        'type' => 'docid',
                        'label' => 'masque e_tst_sl_archivee',
                        'format' => 'MASK',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_colore_tst_sl_archivee' =>
                    array(
                        'type' => 'color',
                        'label' => 'couleur e_tst_sl_archivee',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_cvide_tst_sl_archivee' =>
                    array(
                        'type' => 'docid',
                        'label' => 'contrôle de vue e_tst_sl_archivee',
                        'format' => 'CVDOC',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mtide_tst_sl_archivee' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel e_tst_sl_archivee',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_tmide_tst_sl_archivee' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur e_tst_sl_archivee',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fre_tst_sl_validee_directeur' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Paramètre de l\'étape e_tst_sl_validee_directeur',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_ide_tst_sl_validee_directeur' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Profil e_tst_sl_validee_directeur',
                        'format' => 'PROFIL',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fallide_tst_sl_validee_directeur' =>
                    array(
                        'type' => 'docid',
                        'label' => 'e_tst_sl_validee_directeur field access list',
                        'format' => 'FIELDACCESSLAYERLIST',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mskide_tst_sl_validee_directeur' =>
                    array(
                        'type' => 'docid',
                        'label' => 'masque e_tst_sl_validee_directeur',
                        'format' => 'MASK',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_colore_tst_sl_validee_directeur' =>
                    array(
                        'type' => 'color',
                        'label' => 'couleur e_tst_sl_validee_directeur',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_cvide_tst_sl_validee_directeur' =>
                    array(
                        'type' => 'docid',
                        'label' => 'contrôle de vue e_tst_sl_validee_directeur',
                        'format' => 'CVDOC',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mtide_tst_sl_validee_directeur' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel e_tst_sl_validee_directeur',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_tmide_tst_sl_validee_directeur' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur e_tst_sl_validee_directeur',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fre_tst_sl_validee_ca' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Paramètre de l\'étape e_tst_sl_validee_ca',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_ide_tst_sl_validee_ca' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Profil e_tst_sl_validee_ca',
                        'format' => 'PROFIL',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fallide_tst_sl_validee_ca' =>
                    array(
                        'type' => 'docid',
                        'label' => 'e_tst_sl_validee_ca field access list',
                        'format' => 'FIELDACCESSLAYERLIST',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mskide_tst_sl_validee_ca' =>
                    array(
                        'type' => 'docid',
                        'label' => 'masque e_tst_sl_validee_ca',
                        'format' => 'MASK',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_colore_tst_sl_validee_ca' =>
                    array(
                        'type' => 'color',
                        'label' => 'couleur e_tst_sl_validee_ca',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_cvide_tst_sl_validee_ca' =>
                    array(
                        'type' => 'docid',
                        'label' => 'contrôle de vue e_tst_sl_validee_ca',
                        'format' => 'CVDOC',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mtide_tst_sl_validee_ca' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel e_tst_sl_validee_ca',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_tmide_tst_sl_validee_ca' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur e_tst_sl_validee_ca',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fre_tst_sl_contrat_signe_recu' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Paramètre de l\'étape e_tst_sl_contrat_signe_recu',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_ide_tst_sl_contrat_signe_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Profil e_tst_sl_contrat_signe_recu',
                        'format' => 'PROFIL',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fallide_tst_sl_contrat_signe_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'e_tst_sl_contrat_signe_recu field access list',
                        'format' => 'FIELDACCESSLAYERLIST',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mskide_tst_sl_contrat_signe_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'masque e_tst_sl_contrat_signe_recu',
                        'format' => 'MASK',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_colore_tst_sl_contrat_signe_recu' =>
                    array(
                        'type' => 'color',
                        'label' => 'couleur e_tst_sl_contrat_signe_recu',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_cvide_tst_sl_contrat_signe_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'contrôle de vue e_tst_sl_contrat_signe_recu',
                        'format' => 'CVDOC',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mtide_tst_sl_contrat_signe_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel e_tst_sl_contrat_signe_recu',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_tmide_tst_sl_contrat_signe_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur e_tst_sl_contrat_signe_recu',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fre_tst_sl_payee_compta' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Paramètre de l\'étape e_tst_sl_payee_compta',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_ide_tst_sl_payee_compta' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Profil e_tst_sl_payee_compta',
                        'format' => 'PROFIL',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fallide_tst_sl_payee_compta' =>
                    array(
                        'type' => 'docid',
                        'label' => 'e_tst_sl_payee_compta field access list',
                        'format' => 'FIELDACCESSLAYERLIST',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mskide_tst_sl_payee_compta' =>
                    array(
                        'type' => 'docid',
                        'label' => 'masque e_tst_sl_payee_compta',
                        'format' => 'MASK',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_colore_tst_sl_payee_compta' =>
                    array(
                        'type' => 'color',
                        'label' => 'couleur e_tst_sl_payee_compta',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_cvide_tst_sl_payee_compta' =>
                    array(
                        'type' => 'docid',
                        'label' => 'contrôle de vue e_tst_sl_payee_compta',
                        'format' => 'CVDOC',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mtide_tst_sl_payee_compta' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel e_tst_sl_payee_compta',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_tmide_tst_sl_payee_compta' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur e_tst_sl_payee_compta',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fre_tst_sl_ar_recu' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Paramètre de l\'étape e_tst_sl_ar_recu',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_ide_tst_sl_ar_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Profil e_tst_sl_ar_recu',
                        'format' => 'PROFIL',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fallide_tst_sl_ar_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'e_tst_sl_ar_recu field access list',
                        'format' => 'FIELDACCESSLAYERLIST',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mskide_tst_sl_ar_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'masque e_tst_sl_ar_recu',
                        'format' => 'MASK',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_colore_tst_sl_ar_recu' =>
                    array(
                        'type' => 'color',
                        'label' => 'couleur e_tst_sl_ar_recu',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_cvide_tst_sl_ar_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'contrôle de vue e_tst_sl_ar_recu',
                        'format' => 'CVDOC',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mtide_tst_sl_ar_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel e_tst_sl_ar_recu',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_tmide_tst_sl_ar_recu' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur e_tst_sl_ar_recu',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fre_tst_sl_ctp_informe' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Paramètre de l\'étape e_tst_sl_ctp_informe',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_ide_tst_sl_ctp_informe' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Profil e_tst_sl_ctp_informe',
                        'format' => 'PROFIL',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fallide_tst_sl_ctp_informe' =>
                    array(
                        'type' => 'docid',
                        'label' => 'e_tst_sl_ctp_informe field access list',
                        'format' => 'FIELDACCESSLAYERLIST',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mskide_tst_sl_ctp_informe' =>
                    array(
                        'type' => 'docid',
                        'label' => 'masque e_tst_sl_ctp_informe',
                        'format' => 'MASK',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_colore_tst_sl_ctp_informe' =>
                    array(
                        'type' => 'color',
                        'label' => 'couleur e_tst_sl_ctp_informe',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_cvide_tst_sl_ctp_informe' =>
                    array(
                        'type' => 'docid',
                        'label' => 'contrôle de vue e_tst_sl_ctp_informe',
                        'format' => 'CVDOC',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mtide_tst_sl_ctp_informe' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel e_tst_sl_ctp_informe',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_tmide_tst_sl_ctp_informe' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur e_tst_sl_ctp_informe',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fre_tst_sl_bap' =>
                    array(
                        'type' => 'frame',
                        'label' => 'Paramètre de l\'étape e_tst_sl_bap',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_ide_tst_sl_bap' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Profil e_tst_sl_bap',
                        'format' => 'PROFIL',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_fallide_tst_sl_bap' =>
                    array(
                        'type' => 'docid',
                        'label' => 'e_tst_sl_bap field access list',
                        'format' => 'FIELDACCESSLAYERLIST',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mskide_tst_sl_bap' =>
                    array(
                        'type' => 'docid',
                        'label' => 'masque e_tst_sl_bap',
                        'format' => 'MASK',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_colore_tst_sl_bap' =>
                    array(
                        'type' => 'color',
                        'label' => 'couleur e_tst_sl_bap',
                        'format' => '',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_cvide_tst_sl_bap' =>
                    array(
                        'type' => 'docid',
                        'label' => 'contrôle de vue e_tst_sl_bap',
                        'format' => 'CVDOC',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_mtide_tst_sl_bap' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel e_tst_sl_bap',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_tmide_tst_sl_bap' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur e_tst_sl_bap',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wf_tab_transitions' =>
                    array(
                        'type' => 'tab',
                        'label' => 'Transitions',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_validation_un_deux' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_validation_un_deux',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_validation_un_deux' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_validation_un_deux',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_validation_un_deux' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_validation_un_deux',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_validation_un_deux' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_validation_un_deux',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_validation_un_deux' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_validation_un_deux',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_archivage_un_huit' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_archivage_un_huit',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_archivage_un_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_archivage_un_huit',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_archivage_un_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_archivage_un_huit',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_archivage_un_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_archivage_un_huit',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_archivage_un_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_archivage_un_huit',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_demande_deux_un' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_demande_deux_un',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_demande_deux_un' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_demande_deux_un',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_demande_deux_un' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_demande_deux_un',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_demande_deux_un' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_demande_deux_un',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_demande_deux_un' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_demande_deux_un',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_validation_deux_trois' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_validation_deux_trois',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_validation_deux_trois' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_validation_deux_trois',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_validation_deux_trois' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_validation_deux_trois',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_validation_deux_trois' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_validation_deux_trois',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_validation_deux_trois' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_validation_deux_trois',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_demande_trois_un' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_demande_trois_un',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_demande_trois_un' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_demande_trois_un',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_demande_trois_un' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_demande_trois_un',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_demande_trois_un' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_demande_trois_un',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_demande_trois_un' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_demande_trois_un',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_validation_trois_quatre' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_validation_trois_quatre',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_validation_trois_quatre' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_validation_trois_quatre',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_validation_trois_quatre' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_validation_trois_quatre',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_validation_trois_quatre' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_validation_trois_quatre',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_validation_trois_quatre' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_validation_trois_quatre',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_archivage_trois_huit' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_archivage_trois_huit',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_archivage_trois_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_archivage_trois_huit',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_archivage_trois_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_archivage_trois_huit',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_archivage_trois_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_archivage_trois_huit',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_archivage_trois_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_archivage_trois_huit',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_paiement_cinq_six' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_paiement_cinq_six',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_paiement_cinq_six' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_paiement_cinq_six',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_paiement_cinq_six' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_paiement_cinq_six',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_paiement_cinq_six' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_paiement_cinq_six',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_paiement_cinq_six' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_paiement_cinq_six',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_reception_six_sept' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_reception_six_sept',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_reception_six_sept' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_reception_six_sept',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_reception_six_sept' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_reception_six_sept',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_reception_six_sept' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_reception_six_sept',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_reception_six_sept' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_reception_six_sept',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_reception_sept_huit' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_reception_sept_huit',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_reception_sept_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_reception_sept_huit',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_reception_sept_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_reception_sept_huit',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_reception_sept_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_reception_sept_huit',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_reception_sept_huit' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_reception_sept_huit',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_validation_rs_ctp_informe' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_validation_rs_ctp_informe',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_validation_rs_ctp_informe' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_validation_rs_ctp_informe',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_validation_rs_ctp_informe' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_validation_rs_ctp_informe',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_validation_rs_ctp_informe' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_validation_rs_ctp_informe',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_validation_rs_ctp_informe' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_validation_rs_ctp_informe',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_ctp_informe_bap' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_ctp_informe_bap',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_ctp_informe_bap' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_ctp_informe_bap',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_ctp_informe_bap' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_ctp_informe_bap',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_ctp_informe_bap' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_ctp_informe_bap',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_ctp_informe_bap' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_ctp_informe_bap',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sout_long_valid_bap_qqb' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sout_long_valid_bap_qqb',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sout_long_valid_bap_qqb' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sout_long_valid_bap_qqb',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sout_long_valid_bap_qqb' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sout_long_valid_bap_qqb',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sout_long_valid_bap_qqb' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sout_long_valid_bap_qqb',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sout_long_valid_bap_qqb' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sout_long_valid_bap_qqb',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_frt_tst_sl_emission_quatre_cinq' =>
                    array(
                        'type' => 'frame',
                        'label' => 'paramètre pour la transition t_tst_sl_emission_quatre_cinq',
                        'format' => null,
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_mtidt_tst_sl_emission_quatre_cinq' =>
                    array(
                        'type' => 'docid',
                        'label' => 'modèle de courriel t_tst_sl_emission_quatre_cinq',
                        'format' => 'MAILTEMPLATE',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_tmidt_tst_sl_emission_quatre_cinq' =>
                    array(
                        'type' => 'docid',
                        'label' => 'minuteur t_tst_sl_emission_quatre_cinq',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'autocreated' => 'yes',
                                'multiple' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pa_tmidt_tst_sl_emission_quatre_cinq' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur persistent t_tst_sl_emission_quatre_cinq',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
                'wd_trans_pu_tmidt_tst_sl_emission_quatre_cinq' =>
                    array(
                        'type' => 'docid',
                        'label' => 'Minuteur à détacher t_tst_sl_emission_quatre_cinq',
                        'format' => 'TIMER',
                        'access' => 'ReadWrite',
                        'options' =>
                            array(
                                'multiple' => 'yes',
                                'autocreated' => 'yes',
                                'relativeOrder' => '::auto',
                            ),
                    ),
            );


        return [
            [
                "TST_I001",
                $struct01
            ],
            [
                "TST_W001",
                $structW01
            ]
        ];
    }
    //endregion
}
