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
                    'label' => 'Paramètre de l\'étape Rédaction',
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
                    'label' => 'Profil Rédaction',
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
                    'label' => 'Rédaction liste d\'accès aux champs',
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
                    'label' => 'masque Rédaction',
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
                    'label' => 'couleur Rédaction',
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
                    'label' => 'contrôle de vue Rédaction',
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
                    'label' => 'modèle de courriel Rédaction',
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
                    'label' => 'minuteur Rédaction',
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
                    'label' => 'Paramètre de l\'étape Validée RS',
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
                    'label' => 'Profil Validée RS',
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
                    'label' => 'Validée RS liste d\'accès aux champs',
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
                    'label' => 'masque Validée RS',
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
                    'label' => 'couleur Validée RS',
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
                    'label' => 'contrôle de vue Validée RS',
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
                    'label' => 'modèle de courriel Validée RS',
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
                    'label' => 'minuteur Validée RS',
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
                    'label' => 'Paramètre de l\'étape Archivée',
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
                    'label' => 'Profil Archivée',
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
                    'label' => 'Archivée liste d\'accès aux champs',
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
                    'label' => 'masque Archivée',
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
                    'label' => 'couleur Archivée',
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
                    'label' => 'contrôle de vue Archivée',
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
                    'label' => 'modèle de courriel Archivée',
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
                    'label' => 'minuteur Archivée',
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
                    'label' => 'Paramètre de l\'étape Validée Directeur',
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
                    'label' => 'Profil Validée Directeur',
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
                    'label' => 'Validée Directeur liste d\'accès aux champs',
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
                    'label' => 'masque Validée Directeur',
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
                    'label' => 'couleur Validée Directeur',
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
                    'label' => 'contrôle de vue Validée Directeur',
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
                    'label' => 'modèle de courriel Validée Directeur',
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
                    'label' => 'minuteur Validée Directeur',
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
                    'label' => 'Paramètre de l\'étape Validée CTP',
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
                    'label' => 'Profil Validée CTP',
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
                    'label' => 'Validée CTP liste d\'accès aux champs',
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
                    'label' => 'masque Validée CTP',
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
                    'label' => 'couleur Validée CTP',
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
                    'label' => 'contrôle de vue Validée CTP',
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
                    'label' => 'modèle de courriel Validée CTP',
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
                    'label' => 'minuteur Validée CTP',
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
                    'label' => 'Paramètre de l\'étape Contrat signé reçu',
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
                    'label' => 'Profil Contrat signé reçu',
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
                    'label' => 'Contrat signé reçu liste d\'accès aux champs',
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
                    'label' => 'masque Contrat signé reçu',
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
                    'label' => 'couleur Contrat signé reçu',
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
                    'label' => 'contrôle de vue Contrat signé reçu',
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
                    'label' => 'modèle de courriel Contrat signé reçu',
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
                    'label' => 'minuteur Contrat signé reçu',
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
                    'label' => 'Paramètre de l\'étape Payée compta',
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
                    'label' => 'Profil Payée compta',
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
                    'label' => 'Payée compta liste d\'accès aux champs',
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
                    'label' => 'masque Payée compta',
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
                    'label' => 'couleur Payée compta',
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
                    'label' => 'contrôle de vue Payée compta',
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
                    'label' => 'modèle de courriel Payée compta',
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
                    'label' => 'minuteur Payée compta',
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
                    'label' => 'Paramètre de l\'étape AR reçu',
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
                    'label' => 'Profil AR reçu',
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
                    'label' => 'AR reçu liste d\'accès aux champs',
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
                    'label' => 'masque AR reçu',
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
                    'label' => 'couleur AR reçu',
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
                    'label' => 'contrôle de vue AR reçu',
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
                    'label' => 'modèle de courriel AR reçu',
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
                    'label' => 'minuteur AR reçu',
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
                    'label' => 'Paramètre de l\'étape CTP informée',
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
                    'label' => 'Profil CTP informée',
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
                    'label' => 'CTP informée liste d\'accès aux champs',
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
                    'label' => 'masque CTP informée',
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
                    'label' => 'couleur CTP informée',
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
                    'label' => 'contrôle de vue CTP informée',
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
                    'label' => 'modèle de courriel CTP informée',
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
                    'label' => 'minuteur CTP informée',
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
                    'label' => 'Paramètre de l\'étape Bon à payer',
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
                    'label' => 'Profil Bon à payer',
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
                    'label' => 'Bon à payer liste d\'accès aux champs',
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
                    'label' => 'masque Bon à payer',
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
                    'label' => 'couleur Bon à payer',
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
                    'label' => 'contrôle de vue Bon à payer',
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
                    'label' => 'modèle de courriel Bon à payer',
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
                    'label' => 'minuteur Bon à payer',
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
                    'label' => 'paramètre pour la transition Validation RS',
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
                    'label' => 'modèle de courriel Validation RS',
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
                    'label' => 'minuteur Validation RS',
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
                    'label' => 'Minuteur persistent Validation RS',
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
                    'label' => 'Minuteur à détacher Validation RS',
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
                    'label' => 'paramètre pour la transition Archivage (E1-E8)',
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
                    'label' => 'modèle de courriel Archivage (E1-E8)',
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
                    'label' => 'minuteur Archivage (E1-E8)',
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
                    'label' => 'Minuteur persistent Archivage (E1-E8)',
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
                    'label' => 'Minuteur à détacher Archivage (E1-E8)',
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
                    'label' => 'paramètre pour la transition Demande de modifications (E2-E1)',
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
                    'label' => 'modèle de courriel Demande de modifications (E2-E1)',
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
                    'label' => 'minuteur Demande de modifications (E2-E1)',
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
                    'label' => 'Minuteur persistent Demande de modifications (E2-E1)',
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
                    'label' => 'Minuteur à détacher Demande de modifications (E2-E1)',
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
                    'label' => 'paramètre pour la transition Validation Directeur',
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
                    'label' => 'modèle de courriel Validation Directeur',
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
                    'label' => 'minuteur Validation Directeur',
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
                    'label' => 'Minuteur persistent Validation Directeur',
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
                    'label' => 'Minuteur à détacher Validation Directeur',
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
                    'label' => 'paramètre pour la transition Demande de modifications de fond',
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
                    'label' => 'modèle de courriel Demande de modifications de fond',
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
                    'label' => 'minuteur Demande de modifications de fond',
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
                    'label' => 'Minuteur persistent Demande de modifications de fond',
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
                    'label' => 'Minuteur à détacher Demande de modifications de fond',
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
                    'label' => 'paramètre pour la transition Validation CTP',
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
                    'label' => 'modèle de courriel Validation CTP',
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
                    'label' => 'minuteur Validation CTP',
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
                    'label' => 'Minuteur persistent Validation CTP',
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
                    'label' => 'Minuteur à détacher Validation CTP',
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
                    'label' => 'paramètre pour la transition Archivage (E3-E8)',
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
                    'label' => 'modèle de courriel Archivage (E3-E8)',
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
                    'label' => 'minuteur Archivage (E3-E8)',
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
                    'label' => 'Minuteur persistent Archivage (E3-E8)',
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
                    'label' => 'Minuteur à détacher Archivage (E3-E8)',
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
                    'label' => 'paramètre pour la transition Paiement',
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
                    'label' => 'modèle de courriel Paiement',
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
                    'label' => 'minuteur Paiement',
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
                    'label' => 'Minuteur persistent Paiement',
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
                    'label' => 'Minuteur à détacher Paiement',
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
                    'label' => 'paramètre pour la transition Réception AR',
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
                    'label' => 'modèle de courriel Réception AR',
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
                    'label' => 'minuteur Réception AR',
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
                    'label' => 'Minuteur persistent Réception AR',
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
                    'label' => 'Minuteur à détacher Réception AR',
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
                    'label' => 'paramètre pour la transition Réception rapport',
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
                    'label' => 'modèle de courriel Réception rapport',
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
                    'label' => 'minuteur Réception rapport',
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
                    'label' => 'Minuteur persistent Réception rapport',
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
                    'label' => 'Minuteur à détacher Réception rapport',
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
                    'label' => 'paramètre pour la transition Informer la CTP',
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
                    'label' => 'modèle de courriel Informer la CTP',
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
                    'label' => 'minuteur Informer la CTP',
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
                    'label' => 'Minuteur persistent Informer la CTP',
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
                    'label' => 'Minuteur à détacher Informer la CTP',
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
                    'label' => 'paramètre pour la transition Validation BAP 2',
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
                    'label' => 'modèle de courriel Validation BAP 2',
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
                    'label' => 'minuteur Validation BAP 2',
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
                    'label' => 'Minuteur persistent Validation BAP 2',
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
                    'label' => 'Minuteur à détacher Validation BAP 2',
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
                    'label' => 'paramètre pour la transition Validation BAP 1',
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
                    'label' => 'modèle de courriel Validation BAP 1',
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
                    'label' => 'minuteur Validation BAP 1',
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
                    'label' => 'Minuteur persistent Validation BAP 1',
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
                    'label' => 'Minuteur à détacher Validation BAP 1',
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
                    'label' => 'paramètre pour la transition Emission contrat',
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
                    'label' => 'modèle de courriel Emission contrat',
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
                    'label' => 'minuteur Emission contrat',
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
                    'label' => 'Minuteur persistent Emission contrat',
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
                    'label' => 'Minuteur à détacher Emission contrat',
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
