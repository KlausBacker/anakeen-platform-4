<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

use Anakeen\Core\SEManager;

/**
 * @author Anakeen
 * @package Dcp\Pu
 */
//require_once 'PU_testcase_dcp_commonfamily.php';

class TestAttributeOrder extends TestCaseDcpCommonFamily
{
    /**
     * import some documents
     * @static
     * @return string[]
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_orderfamilies.ods",
        );
    }

    protected static function getConfigFile()
    {
        return __DIR__ . "/../../Anakeen/TestUnits/Data/testRelativeOrder.xml";
    }

    /**
     * @dataProvider dataOrderAttribute
     * @param string $family
     * @param array $expectedOrders
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     */
    public function testOrderAttribute($family, $expectedOrders)
    {
        /**
         * @var \Anakeen\Core\SmartStructure $fam
         */
        $fam = SEManager::getFamily($family);
        $this->assertTrue($fam && $fam->isAlive(), sprintf("family %s not alive", $family));

        $attributes = $fam->getAttributes();
        $orders = [];
        $k = 0;
        foreach ($attributes as $attribute) {
            if ($attribute && $attribute->id !== \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD) {
                if (isset($expectedOrders[$k])) {
                    $orders[$expectedOrders[$k]] = $attribute->id;
                } else {
                    $orders[] = $attribute->id;
                }
                $k++;
            }
        }
        /**
         * @var \Anakeen\Core\SmartStructure\BasicAttribute $prevAttr
         */
        $prevAttr = null;
        $k = 0;
        foreach ($orders as $attrid) {
            $this->assertEquals(
                strtolower($expectedOrders[$k] ?? null),
                $attrid,
                sprintf("Not good found %s > %s : %s", $attrid, $expectedOrders[$k], print_r($orders, true))
            );
            $k++;
        }
    }

    /**
     * @dataProvider dataRelativeOverrideOrderAttribute
     * @param string $family
     * @param array $expectedOrders
     */
    public function testRelativeOverrideOrderAttribute($family, $expectedOrders)
    {
        $this->testOrderAttribute($family, $expectedOrders);
    }

    /**
     * @dataProvider dataOptAttribute
     * @param string $family
     * @param array $expectedOpts
     * @throws \Anakeen\Core\Exception
     */
    public function testOptAttribute($family, $expectedOpts)
    {
        /**
         * @var \Anakeen\Core\SmartStructure $fam
         */
        $fam = SEManager::getFamily($family);
        $this->assertTrue($fam->isAlive(), sprintf("family %s not alive", $family));

        foreach ($expectedOpts as $attrid => $opts) {
            $attr = $fam->getAttribute($attrid);
            $this->assertNotEmpty($attr, "Attribute $attrid not exists");
            foreach ($opts as $kopt => $opt) {
                $this->assertEquals(
                    $opt,
                    $attr->getOption($kopt),
                    sprintf(
                        "Verify \"%s\" on \"%s\". Has : %s",
                        $kopt,
                        $attrid,
                        print_r($attr->getOptions(), true)
                    )
                );
            }
        }
    }


    public function dataRelativeOverrideOrderAttribute()
    {
        $order01 = [
            'tst01_t_histo',
            'tst01_f_acteurs',
            'tst01_createur',
            'tst01_createur_title',
            'tst01_date_creation',
            'tst01_doc_access_grp',
            'tst01_doc_access_grp_title',
            'tst01_f_historique',
            'tst01_date_reprise',
            'tst01_a_historique',
            'tst01_historique_date',
            'tst01_historique_auteur',
            'tst01_historique_auteur_title',
            'tst01_historique_commentaire',
            'tst01_historique_documents',
            'tst01_historique_documents_title',

        ];
        $order02a = [
            'tst02_t_attachments',
            'tst02_f_pieces_jointes',
            'tst02_a_fichiers_associes',
            'tst02_fichier_attache_file',
            'tst02_fichier_attache_titre',
            'tst02_t_complements',
            'tst02_f_liens',
            'tst02_a_voir_aussi',
            'tst02_voir_aussi_relation',
            'tst02_voir_aussi_document',
            'tst02_voir_aussi_document_title',
            'tst02_libelle_document',
            'tst02_a_voir_aussi_fige',
            'tst02_voir_aussi_relation_fige',
            'tst02_voir_aussi_document_fige',
            'tst02_voir_aussi_document_fige_title',
            'tst02_libelle_document_fige',
            'tst02_a_ref_par',
            'tst02_ref_par_document',
            'tst02_ref_par_document_title',
            'tst02_a_ref_par_fige',
            'tst02_ref_par_document_fige',
            'tst02_ref_par_document_fige_title',
            'tst02_ref_par_document_fige_initid',
            'tst02_ref_par_document_fige_initid_title',
            'tst02_f_caracterisation',
            'tst02_ref',
            'tst02_f_details',
            'tst02_visibility',
            'tst02_extra_visibility',
            'tst02_extra_visibility_title',
            'tst02_visibility_authorized_accounts',
            'tst02_visibility_authorized_accounts_title',
            'tst02_t_rattachements',
            'tst02_f_domaine',
            'tst02_domaine_app',
            'tst02_domaine_app_title',
            'tst02_all_domaines_app',
            'tst02_all_domaines_app_title',
            'tst02_domaine_app_search',
            'tst02_domaine_app_search_title',
            'tst02_f_processus',
            'tst02_processus',
            'tst02_processus_title',
            'tst02_processus_parents',
            'tst02_processus_parents_title',
            'tst02_activite',
            'tst02_activite_title',
            'tst02_operation',
            'tst02_operation_title',
            'tst02_f_systeme_mng',
            'tst02_systeme_mng',
            'tst02_systeme_mng_title',
        ];
        $order02b = [
            'tst02_t_commentaires',
            'tst02_f_commentaires',
            'tst02_a_commentaires',
            'tst02_commentaire_utilisateur',
            'tst02_commentaire_utilisateur_title',
            'tst02_commentaire_commentaire',
            'tst02_commentaire_commentaire_title',
            'tst02_commentaire_etat',
            'tst02_commentaire_fichier',
            'tst02_f_notes_gestionnaires',
            'tst02_a_notes_gestionnaires',
            'tst02_note_gestionnaire_note',
            'tst02_note_gestionnaire_note_title',
            'tst02_note_gestionnaire_utilisateur',
            'tst02_note_gestionnaire_utilisateur_title',
            'tst02_f_general',
            'tst02_entite_entretien',
            'tst02_entite_entretien_title',
            'tst02_ref_interne',
            'tst02_reference',
            'tst02_parametres',
            'tst02_a_closed_states',
            'tst02_closed_states_keys',
        ];
        $order02 = array_merge($order02a, $order01, $order02b);
        $order03 = array(
            'tst03_f_fichier_attache',
            'tst03_fichier_attache_file',
            'tst03_lien_fichier',
            'tst03_lien_indication',
            'tst02_t_attachments',
            'tst02_f_pieces_jointes',
            'tst02_a_fichiers_associes',
            'tst02_fichier_attache_file',
            'tst02_fichier_attache_titre',
            'tst03_a_annexes',
            'tst03_annexe_fichier',
            'tst03_annexe_titre',
            'tst03_a_sources',
            'tst03_sources_fichier',
            'tst03_sources_titre',
            'tst02_t_complements',
            'tst02_f_liens',
            'tst02_a_voir_aussi',
            'tst02_voir_aussi_relation',
            'tst02_voir_aussi_document',
            'tst02_voir_aussi_document_title',
            'tst02_libelle_document',
            'tst02_a_voir_aussi_fige',
            'tst02_voir_aussi_relation_fige',
            'tst02_voir_aussi_document_fige',
            'tst02_voir_aussi_document_fige_title',
            'tst02_libelle_document_fige',
            'tst02_a_ref_par',
            'tst02_ref_par_document',
            'tst02_ref_par_document_title',
            'tst02_a_ref_par_fige',
            'tst02_ref_par_document_fige',
            'tst02_ref_par_document_fige_title',
            'tst02_ref_par_document_fige_initid',
            'tst02_ref_par_document_fige_initid_title',
            'tst02_f_caracterisation',
            'tst02_ref',
            'tst03_docu_externe',
            'tst03_f_details',
            'tst03_commentaire',
            'tst02_f_details',
            'tst02_visibility',
            'tst02_extra_visibility',
            'tst02_extra_visibility_title',
            'tst02_visibility_authorized_accounts',
            'tst02_visibility_authorized_accounts_title',
            'tst02_t_rattachements',
            'tst02_f_domaine',
            'tst02_domaine_app',
            'tst02_all_domaines_app',
            'tst02_domaine_app_search',
            'tst02_domaine_app_title',
            'tst02_all_domaines_app_title',
            'tst02_domaine_app_search_title',
            'tst02_f_processus',
            'tst02_processus',
            'tst02_processus_title',
            'tst02_processus_parents',
            'tst02_processus_parents_title',
            'tst02_activite',
            'tst02_activite_title',
            'tst02_operation',
            'tst02_operation_title',
            'tst02_f_systeme_mng',
            'tst02_systeme_mng',
            'tst02_systeme_mng_title',
            'tst03_f_pole_metiers',
            'tst03_pole',
            'tst03_pole_title',
            'tst03_metiers',
            'tst03_metiers_title',
            'tst01_t_histo',
            'tst01_f_acteurs',
            'tst01_createur',
            'tst01_createur_title',
            'tst01_date_creation',
            'tst03_redacteur',
            'tst03_redacteur_title',
            'tst01_doc_access_grp',
            'tst01_doc_access_grp_title',
            'tst03_approbateur',
            'tst03_date_approbation',
            'tst03_gestionnaire',
            'tst03_gestionnaire_title',
            'tst03_gestionnaires_da',
            'tst03_gestionnaires_da_title',
            'tst03_approbateur_title',
            'tst03_f_anciennes_versions',
            'tst03_a_anciennes_versions',
            'tst03_ancienne_version_document',
            'tst03_ancienne_version_document_title',
            'tst03_ancienne_version_date_version',
            'tst03_ancienne_version_document_travail',
            'tst03_ancienne_version_document_travail_title',
            'tst03_ancienne_version_edition',
            'tst01_f_historique',
            'tst01_date_reprise',
            'tst01_a_historique',
            'tst01_historique_date',
            'tst01_historique_auteur',
            'tst01_historique_auteur_title',
            'tst01_historique_commentaire',
            'tst01_historique_documents',
            'tst01_historique_documents_title',
            'tst03_t_gestion',
            'tst03_f_gestion',
            'tst02_entite_entretien',
            'tst03_date_limite_revue',
            'tst03_t_enregistrements',
            'tst03_f_enregistrements',
            'tst03_a_enregistrements',
            'tst03_enregistrement',
            'tst03_enregistrement_identification',
            'tst03_enregistrement_stockage',
            'tst03_enregistrement_protection',
            'tst03_enregistrement_accessibilite',
            'tst03_enregistrement_duree_conservation',
            'tst03_enregistrement_elimination',
            'tst02_t_commentaires',
            'tst02_f_commentaires',
            'tst02_a_commentaires',
            'tst02_commentaire_utilisateur',
            'tst02_commentaire_utilisateur_title',
            'tst02_commentaire_commentaire',
            'tst02_commentaire_commentaire_title',
            'tst02_commentaire_etat',
            'tst02_commentaire_fichier',
            'tst02_f_notes_gestionnaires',
            'tst02_a_notes_gestionnaires',
            'tst02_note_gestionnaire_note',
            'tst02_note_gestionnaire_note_title',
            'tst02_note_gestionnaire_utilisateur',
            'tst02_note_gestionnaire_utilisateur_title',
            'tst02_f_general',
            'tst03_titre',
            'tst02_ref_interne',
            'tst03_intitule',
            'tst02_reference',
            'tst03_edition',
            'tst03_date_publication',
            'tst03_type_doc',
            'tst03_type_doc_title',
            'tst03_origine',
            'tst02_parametres',
            'tst02_a_closed_states',
            'tst02_closed_states_keys',
            'tst02_entite_entretien_title',
        );
        $order04=array (
            'tst04_t_document',
            'tst03_f_fichier_attache',
            'tst02_f_caracterisation',
            'tst02_f_general',
            'tst03_lien_indication',
            'tst04_declinaisons_document',
            'tst04_declinaisons_document_title',
            'tst04_applicabilite',
            'tst04_decline',
            'tst04_decline_title',
            'tst04_date_abrogation',
            'tst04_documents_abrogateurs',
            'tst04_documents_abrogateurs_title',
            'tst04_last_dt',
            'tst02_f_details',
            'tst04_justification_abrogation',
            'tst02_f_pieces_jointes',
            'tst04_f_annexes',
            'tst03_a_annexes',
            'tst04_f_sources',
            'tst03_a_sources',
            'tst03_f_details',
            'tst02_t_attachments',
            'tst02_t_complements',
            'tst02_f_liens',
            'tst02_a_voir_aussi',
            'tst02_voir_aussi_relation',
            'tst02_voir_aussi_document',
            'tst02_voir_aussi_document_title',
            'tst02_libelle_document',
            'tst02_a_voir_aussi_fige',
            'tst02_voir_aussi_relation_fige',
            'tst02_voir_aussi_document_fige',
            'tst02_voir_aussi_document_fige_title',
            'tst02_libelle_document_fige',
            'tst02_a_ref_par',
            'tst02_ref_par_document',
            'tst02_ref_par_document_title',
            'tst02_a_ref_par_fige',
            'tst02_ref_par_document_fige',
            'tst02_ref_par_document_fige_title',
            'tst02_ref_par_document_fige_initid',
            'tst02_ref_par_document_fige_initid_title',
            'tst02_t_rattachements',
            'tst02_f_domaine',
            'tst02_domaine_app',
            'tst02_domaine_app_title',
            'tst02_all_domaines_app_title',
            'tst02_domaine_app_search_title',
            'tst02_all_domaines_app',
            'tst02_domaine_app_search',
            'tst04_domaines_application_rattachement',
            'tst04_domaines_application_rattachement_title',
            'tst02_f_processus',
            'tst02_processus',
            'tst02_processus_title',
            'tst02_processus_parents',
            'tst02_processus_parents_title',
            'tst02_activite',
            'tst02_activite_title',
            'tst02_operation',
            'tst02_operation_title',
            'tst02_f_systeme_mng',
            'tst02_systeme_mng',
            'tst02_systeme_mng_title',
            'tst03_f_pole_metiers',
            'tst03_pole',
            'tst03_pole_title',
            'tst03_metiers',
            'tst03_metiers_title',
            'tst01_t_histo',
            'tst01_f_acteurs',
            'tst01_createur',
            'tst01_createur_title',
            'tst01_date_creation',
            'tst03_redacteur',
            'tst03_redacteur_title',
            'tst01_doc_access_grp',
            'tst01_doc_access_grp_title',
            'tst03_approbateur',
            'tst03_date_approbation',
           'tst03_gestionnaire',
           'tst03_gestionnaires_da',
           'tst04_a_dernieres_verifications',
           'tst04_dernier_verificateur',
           'tst03_gestionnaires_da_title',
           'tst03_gestionnaire_title',
           'tst03_approbateur_title',
           'tst03_f_anciennes_versions',
           'tst03_a_anciennes_versions',
           'tst03_ancienne_version_document',
           'tst03_ancienne_version_document_title',
           'tst03_ancienne_version_date_version',
           'tst03_ancienne_version_document_travail',
           'tst03_ancienne_version_document_travail_title',
           'tst03_ancienne_version_edition',
           'tst03_t_gestion',
           'tst03_f_gestion',
           'tst02_entite_entretien',
           'tst03_date_limite_revue',
           'tst04_doc_travail_courant',
           'tst04_doc_travail_courant_title',
           'tst04_f_accuses_lecture',
           'tst04_a_accuses_lecture',
           'tst04_accuse_lecture_personne',
           'tst04_accuse_lecture_date',
           'tst03_t_enregistrements',
           'tst03_f_enregistrements',
           'tst03_a_enregistrements',
           'tst03_enregistrement',
           'tst03_enregistrement_identification',
           'tst03_enregistrement_stockage',
           'tst03_enregistrement_protection',
           'tst03_enregistrement_accessibilite',
           'tst03_enregistrement_duree_conservation',
           'tst03_enregistrement_elimination',
           'tst04_t_historique',
           'tst01_f_historique',
           'tst02_t_commentaires',
           'tst02_f_commentaires',
           'tst02_a_commentaires',
           'tst02_commentaire_utilisateur',
           'tst02_commentaire_utilisateur_title',
           'tst02_commentaire_commentaire',
           'tst02_commentaire_commentaire_title',
           'tst02_commentaire_etat',
           'tst02_commentaire_fichier',
           'tst02_f_notes_gestionnaires',
           'tst02_a_notes_gestionnaires',
           'tst02_note_gestionnaire_note',
           'tst02_note_gestionnaire_note_title',
           'tst02_note_gestionnaire_utilisateur',
           'tst02_note_gestionnaire_utilisateur_title',
           'tst02_parametres',
           'tst02_a_closed_states',
           'tst02_closed_states_keys',
           'tst01_date_reprise',
           'tst01_a_historique',
           'tst02_a_fichiers_associes',
           'tst02_ref',
           'tst02_visibility',
           'tst02_extra_visibility_title',
           'tst02_extra_visibility',
           'tst02_visibility_authorized_accounts_title',
           'tst02_visibility_authorized_accounts',
           'tst02_entite_entretien_title',
           'tst02_ref_interne',
           'tst02_reference',
           'tst03_fichier_attache_file',
           'tst03_lien_fichier',
           'tst03_docu_externe',
           'tst03_commentaire',
           'tst03_titre',
           'tst03_intitule',
           'tst03_edition',
           'tst03_date_publication',
           'tst03_type_doc',
           'tst03_type_doc_title',
           'tst03_origine',
           'tst04_commentaire_fichier',
           'tst01_historique_date',
           'tst01_historique_auteur',
           'tst01_historique_auteur_title',
           'tst01_historique_commentaire',
           'tst01_historique_documents_title',
           'tst01_historique_documents',
           'tst02_fichier_attache_file',
           'tst02_fichier_attache_titre',
           'tst03_annexe_fichier',
           'tst03_annexe_titre',
           'tst03_sources_fichier',
           'tst03_sources_titre',
        );

        $order05=array (
            'tst05_t_consignes',
            'tst05_f_consignes',
            'tst05_modif_en_cours',
            'tst04_t_document',
            'tst02_f_general',
            'tst03_fichier_attache_file',
            'tst03_lien_indication',
            'tst04_declinaisons_document',
            'tst04_declinaisons_document_title',
            'tst04_applicabilite',
            'tst04_decline',
            'tst04_date_abrogation',
            'tst04_documents_abrogateurs',
            'tst04_documents_abrogateurs_title',
            'tst04_decline_title',
            'tst04_last_dt',
            'tst05_date_limite_redaction',
            'tst03_origine',
            'tst03_commentaire',
            'tst02_f_details',
            'tst04_justification_abrogation',
            'tst03_f_fichier_attache',
            'tst02_f_pieces_jointes',
            'tst02_f_caracterisation',
            'tst02_ref',
            'tst03_f_details',
            'tst04_f_annexes',
            'tst03_a_annexes',
            'tst04_f_sources',
            'tst03_a_sources',
            'tst01_t_histo',
            'tst01_f_acteurs',
            'tst01_createur',
            'tst01_createur_title',
            'tst01_date_creation',
            'tst03_redacteur',
            'tst03_redacteur_title',
            'tst01_doc_access_grp',
            'tst01_doc_access_grp_title',
            'tst03_approbateur',
            'tst03_date_approbation',
            'tst03_gestionnaire',
            'tst03_gestionnaires_da',
            'tst04_a_dernieres_verifications',
            'tst04_dernier_verificateur',
            'tst04_derniere_decision',
            'tst03_gestionnaires_da_title',
            'tst03_gestionnaire_title',
            'tst03_approbateur_title',
            'tst03_f_anciennes_versions',
            'tst03_a_anciennes_versions',
            'tst03_ancienne_version_document',
            'tst03_ancienne_version_document_title',
            'tst03_ancienne_version_date_version',
            'tst03_ancienne_version_document_travail',
            'tst03_ancienne_version_document_travail_title',
            'tst03_ancienne_version_edition',
            'tst05_f_participants',
            'tst05_anciens_participants',
            'tst05_anciens_participants_title',
            'tst05_participants_recueil_default',
            'tst05_participants_recueil_default_title',
            'tst05_participants_verification_default',
            'tst05_participants_verification_default_title',
            'tst03_t_enregistrements',
            'tst03_f_enregistrements',
            'tst03_a_enregistrements',
            'tst03_enregistrement',
            'tst03_enregistrement_identification',
            'tst03_enregistrement_stockage',
            'tst03_enregistrement_protection',
            'tst03_enregistrement_accessibilite',
            'tst03_enregistrement_duree_conservation',
            'tst03_enregistrement_elimination',
            'tst02_t_attachments',
            'tst02_t_complements',
            'tst02_f_liens',
            'tst02_a_voir_aussi',
            'tst02_voir_aussi_relation',
            'tst02_voir_aussi_document',
            'tst02_voir_aussi_document_title',
            'tst02_libelle_document',
            'tst02_a_voir_aussi_fige',
            'tst02_voir_aussi_relation_fige',
            'tst02_voir_aussi_document_fige',
            'tst02_voir_aussi_document_fige_title',
            'tst02_libelle_document_fige',
            'tst02_a_ref_par',
            'tst02_ref_par_document',
            'tst02_ref_par_document_title',
            'tst02_a_ref_par_fige',
            'tst02_ref_par_document_fige',
            'tst02_ref_par_document_fige_title',
            'tst02_ref_par_document_fige_initid',
            'tst02_ref_par_document_fige_initid_title',
            'tst02_t_rattachements',
            'tst02_f_domaine',
            'tst02_domaine_app_title',
            'tst02_all_domaines_app_title',
            'tst02_domaine_app_search_title',
             'tst02_all_domaines_app',
             'tst02_domaine_app_search',
             'tst02_domaine_app',
             'tst04_domaines_application_rattachement',
             'tst04_domaines_application_rattachement_title',
             'tst02_f_processus',
             'tst02_processus',
             'tst02_processus_title',
             'tst02_processus_parents',
             'tst02_processus_parents_title',
             'tst02_activite',
             'tst02_activite_title',
             'tst02_operation',
             'tst02_operation_title',
             'tst02_f_systeme_mng',
             'tst02_systeme_mng',
             'tst02_systeme_mng_title',
             'tst03_f_pole_metiers',
             'tst03_pole',
             'tst03_pole_title',
             'tst03_metiers',
             'tst03_metiers_title',
             'tst03_t_gestion',
             'tst03_f_gestion',
             'tst02_entite_entretien',
             'tst03_date_limite_revue',
             'tst04_doc_travail_courant',
             'tst05_docref_source',
             'tst05_docref_source_title',
             'tst04_doc_travail_courant_title',
             'tst04_f_accuses_lecture',
             'tst04_a_accuses_lecture',
             'tst04_accuse_lecture_personne',
             'tst04_accuse_lecture_date',
             'tst04_t_historique',
             'tst01_f_historique',
             'tst02_t_commentaires',
             'tst02_f_commentaires',
             'tst02_a_commentaires',
             'tst02_commentaire_utilisateur',
             'tst02_commentaire_utilisateur_title',
             'tst02_commentaire_commentaire',
             'tst02_commentaire_commentaire_title',
             'tst02_commentaire_etat',
             'tst02_commentaire_fichier',
             'tst02_f_notes_gestionnaires',
             'tst02_a_notes_gestionnaires',
             'tst02_note_gestionnaire_note',
             'tst02_note_gestionnaire_note_title',
             'tst02_note_gestionnaire_utilisateur',
             'tst02_note_gestionnaire_utilisateur_title',
             'tst02_parametres',
             'tst02_a_closed_states',
             'tst02_closed_states_keys',
             'tst05_t_complement_gestionnaire',
             'tst05_f_complement_gestionnaire',
             'tst03_type_doc',
             'tst05_ref_min',
             'tst01_date_reprise',
             'tst01_a_historique',
             'tst02_a_fichiers_associes',
             'tst02_visibility',
             'tst02_extra_visibility',
             'tst02_extra_visibility_title',
             'tst02_visibility_authorized_accounts',
             'tst02_visibility_authorized_accounts_title',
             'tst02_entite_entretien_title',
             'tst02_ref_interne',
             'tst02_reference',
             'tst03_lien_fichier',
             'tst03_docu_externe',
             'tst03_titre',
             'tst03_intitule',
             'tst03_edition',
             'tst03_date_publication',
             'tst03_type_doc_title',
             'tst04_commentaire_fichier',
             'tst01_historique_date',
             'tst01_historique_auteur_title',
             'tst01_historique_auteur',
             'tst01_historique_commentaire',
             'tst01_historique_documents',
             'tst01_historique_documents_title',
             'tst02_fichier_attache_file',
             'tst02_fichier_attache_titre',
             'tst03_annexe_fichier',
             'tst03_annexe_titre',
             'tst03_sources_fichier',
             'tst03_sources_titre',
        );

        return array(
            ["TST_ORDER_01", $order01],
            ["TST_ORDER_02", $order02],
            ["TST_ORDER_03", $order03],
            ["TST_ORDER_04", $order04],
            ["TST_ORDER_05", $order05]
        );
    }

    public function dataOrderAttribute()
    {
        $aOrder = array(
            "TST_AF1000",
            "TST_A2000",
            "TST_A3000",
            "TST_A4000",
            "TST_AA5000",
            "TST_A6000",
            "TST_A7000",
            "TST_AT8000",
            "TST_AF9000",
            "TST_A10000",
            "TST_A11000",
            "TST_A12000",
            "TST_AF13000",
            "TST_A14000",
            "TST_A15000",
            "TST_AA16000",
            "TST_A17000",
            "TST_A18000",
            "TST_A19000"
        );
        $bOrder = array(
            "TST_BC500",
            "TST_B550",
            "TST_B600",
            "TST_AF1000",
            "TST_A2000",
            "TST_B2500",
            "TST_A3000",
            "TST_A4000",
            "TST_AA5000",
            "TST_A6000",
            "TST_A7000",
            "TST_B7100",
            "TST_B7200",
            "TST_BT7300",
            "TST_BF7400",
            "TST_B7500",
            "TST_B7600",
            "TST_AT8000",
            "TST_AF9000",
            "TST_A10000",
            "TST_A11000",
            "TST_A12000",
            "TST_AF13000",
            "TST_A14000",
            "TST_A15000",
            "TST_AA16000",
            "TST_A17000",
            "TST_A18000",
            "TST_A19000",
            "TST_BT20000",
            "TST_BF21000",
            "TST_B22000",
            "TST_B23000"
        );
        $cOrder = array(
            "TST_BC500",
            "TST_B550",
            "TST_B600",
            "TST_AF1000",
            "TST_A2000",
            "TST_B2500",
            "TST_A3000",
            "TST_A4000",
            "TST_AA5000",
            "TST_A6000",
            "TST_A7000",
            "TST_B7100",
            "TST_B7200",
            "TST_BT7300",
            "TST_BF7400",
            "TST_B7500",
            "TST_B7600",
            "TST_AT8000",
            "TST_AF9000",
            "TST_A10000",
            "TST_A11000",
            "TST_A12000",
            "TST_AF13000",
            "TST_A14000",
            "TST_A15000",
            "TST_AA16000",
            "TST_A17000",
            "TST_A18000",
            "TST_A19000",
            "TST_CT19500",
            "TST_CF19600",
            "TST_C19700",
            "TST_C19800",
            "TST_BT20000",
            "TST_BF21000",
            "TST_B22000",
            "TST_B23000"
        );
        $dOrder = array(
            "TST_BC500",
            "TST_B600",
            "TST_B550",
            "TST_AF1000",
            "TST_A2000",
            "TST_B2500",
            "TST_A3000",
            "TST_A4000",
            "TST_AA5000",
            "TST_A6000",
            "TST_A7000",
            "TST_B7100",
            "TST_B7200",
            "TST_BT7300",
            "TST_BF7400",
            "TST_B7500",
            "TST_B7600",
            "TST_AT8000",
            "TST_AF13000",
            "TST_A14000",
            "TST_A15000",
            "TST_AA16000",
            "TST_A17000",
            "TST_A18000",
            "TST_A19000",
            "TST_AF9000",
            "TST_A10000",
            "TST_A11000",
            "TST_A12000",
            "TST_BT20000",
            "TST_BF21000",
            "TST_B22000",
            "TST_B23000"
        );
        $eOrder = array_merge($cOrder, ["TST_ET25000", "TST_EF25100", "TST_E25200"]);
        return array(

            array(
                "TST_ORDERAUTOA",
                $aOrder
            ),
            array(
                "TST_ORDERAUTOB",
                $bOrder
            ),
            array(
                "TST_ORDERAUTOC",
                $cOrder
            ),
            array(
                "TST_ORDERAUTOD",
                $dOrder
            ),
            array(
                "TST_ORDERAUTOE",
                $eOrder
            ),
            array(
                "TST_ORDERRELA",
                $aOrder
            ),
            array(
                "TST_ORDERRELB",
                $bOrder
            ),
            array(
                "TST_ORDERRELC",
                $cOrder
            ),
            array(
                "TST_ORDERRELD",
                $dOrder
            ),
            array(
                "TST_ORDERRELE",
                $eOrder
            ),
            array(
                "TST_ORDERNUMA",
                $aOrder
            ),
            array(
                "TST_ORDERNUMB",
                $bOrder
            ),
            array(
                "TST_ORDERNUMC",
                $cOrder
            ),
            array(
                "TST_ORDERNUMD",
                $dOrder
            ),
            array(
                "TST_ORDERNUME",
                $eOrder
            )
        );
    }

    public function dataOptAttribute()
    {
        return array(
            array(
                "TST_ORDERAUTOA",
                [
                    "TST_A2000" => ["customopt" => "2000"],
                    "TST_AF9000" => ["customopt" => "9000"]
                ]
            ),
            array(
                "TST_ORDERAUTOB",
                [
                    "TST_A2000" => ["customopt" => "2000"],
                    "TST_AF9000" => ["customopt" => "9000"],
                    "TST_B600" => ["customopt" => "600"],
                ]
            ),
            array(
                "TST_ORDERAUTOC",
                [
                    "TST_A2000" => ["customopt" => "2000"],
                    "TST_AF9000" => ["customopt" => "9000"],
                    "TST_B600" => ["customoptbis" => "600", "customopt" => "600"],
                ]
            ),
            array(
                "TST_ORDERAUTOD",
                [
                    "TST_A2000" => ["customoptbis" => "2000", "customopt" => ""],
                    "TST_AF9000" => ["customopt" => "9000"]
                ]
            )
        );
    }
}
