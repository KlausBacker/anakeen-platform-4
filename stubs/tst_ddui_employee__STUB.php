<?php
namespace Dcp\Family {
	/** Employé  */
	class Tst_ddui_employee extends Document { const familyName="TST_DDUI_EMPLOYEE";}
}

namespace Dcp\AttributeIdentifiers {
	/** Employé  */
	class Tst_ddui_employee {
		/** [tab] Identité */
		const tst_t_identite='tst_t_identite';
		/** [frame] Identité */
		const tst_f_identite='tst_f_identite';
		/** [enum] Civilité */
		const tst_civilite='tst_civilite';
		/** [text] Nom */
		const tst_nom='tst_nom';
		/** [text] Nom de naissance */
		const tst_nom_naissance='tst_nom_naissance';
		/** [text] Prénom */
		const tst_prenom='tst_prenom';
		/** [text] Courriel de correspondance principal */
		const tst_mail_principal='tst_mail_principal';
		/** [text] Courriel de correspondance secondaire */
		const tst_mail_secondaire='tst_mail_secondaire';
		/** [date] Date de naissance */
		const tst_nais_date='tst_nais_date';
		/** [enum] Nationalité */
		const tst_nationalite='tst_nationalite';
		/** [tab] Statut */
		const tst_t_statut='tst_t_statut';
		/** [frame] Statut de l'employé */
		const tst_f_statut='tst_f_statut';
		/** [enum] Statut */
		const tst_statut='tst_statut';
		/** [date] Depuis quand */
		const tst_statut_since='tst_statut_since';
		/** [enum] Pays d'exercice */
		const tst_stranger='tst_stranger';
		/** [enum] Secteur d'activité */
		const tst_emp_pubpriv='tst_emp_pubpriv';
		/** [tab] Situation professionnelle */
		const tst_t_situation_professionnelle='tst_t_situation_professionnelle';
		/** [frame] Situation professionnelle */
		const tst_f_situation_professionnelle='tst_f_situation_professionnelle';
		/** [enum] Votre situation professionnelle a évolué */
		const tst_emp_evo='tst_emp_evo';
		/** [date] Date de fin du poste précédent */
		const tst_emp_date_fin_pre='tst_emp_date_fin_pre';
		/** [enum] Corps/Grade */
		const tst_emp_corps='tst_emp_corps';
		/** [text] Titre */
		const tst_emp_titre='tst_emp_titre';
		/** [text] Établissement de rattachement référencé */
		const tst_emp_idetab='tst_emp_idetab';
		/** [int] Depuis (année) */
		const tst_exp_since='tst_exp_since';
		/** [text] Laboratoire de rattachement */
		const tst_exp_labo_rat='tst_exp_labo_rat';
		/** [text] Acronyme du laboratoire */
		const tst_exp_labo_acro='tst_exp_labo_acro';
		/** [text] Adresse professionnelle (ligne 1) */
		const tst_adw_adresse1='tst_adw_adresse1';
		/** [text] Adresse professionnelle (ligne 2) */
		const tst_adw_adresse2='tst_adw_adresse2';
		/** [text] Adresse professionnelle (ligne 3) */
		const tst_adw_adresse3='tst_adw_adresse3';
		/** [text] Code postal professionnel */
		const tst_adw_codepostal='tst_adw_codepostal';
		/** [text] Ville professionnelle */
		const tst_adw_localite='tst_adw_localite';
		/** [enum] Pays professionnel */
		const tst_adw_pays='tst_adw_pays';
		/** [array] Téléphones professionnels */
		const tst_a_emp_tel='tst_a_emp_tel';
		/** [enum] Type */
		const tst_emp_tel_type='tst_emp_tel_type';
		/** [text] Numéro */
		const tst_emp_tel_number='tst_emp_tel_number';
		/** [file] CV (fichier) */
		const tst_cvfile='tst_cvfile';
		/** [tab] Compétences */
		const tst_t_competences='tst_t_competences';
		/** [frame] Langues */
		const tst_f_langues='tst_f_langues';
		/** [array] Langues */
		const tst_a_langues='tst_a_langues';
		/** [text] Langue */
		const tst_lang='tst_lang';
		/** [enum] Lu */
		const tst_lang_lu='tst_lang_lu';
		/** [enum] Écrit */
		const tst_lang_ecrit='tst_lang_ecrit';
		/** [enum] Parlé */
		const tst_lang_parle='tst_lang_parle';
		/** [tab] Parcours professionnel */
		const tst_t_parcours_professionnel='tst_t_parcours_professionnel';
		/** [frame] Parcours professionnel */
		const tst_f_parcours_professionnel='tst_f_parcours_professionnel';
		/** [array] Parcours professionnel */
		const tst_a_parcours_professionnel='tst_a_parcours_professionnel';
		/** [int] Année de début */
		const tst_parc_pro_begin_date='tst_parc_pro_begin_date';
		/** [int] Année de fin */
		const tst_parc_pro_end_date='tst_parc_pro_end_date';
		/** [text] Titre */
		const tst_parc_pro_title='tst_parc_pro_title';
		/** [text] Établissement */
		const tst_parc_pro_etab='tst_parc_pro_etab';
		/** [frame] Responsabilités exercées */
		const tst_f_responsabilites='tst_f_responsabilites';
		/** [array] Responsabilités exercées */
		const tst_a_responsabilites='tst_a_responsabilites';
		/** [int] Année de début */
		const tst_responsabilite_begin_date='tst_responsabilite_begin_date';
		/** [int] Année de fin */
		const tst_responsabilite_end_date='tst_responsabilite_end_date';
		/** [enum] Responsabilité */
		const tst_responsabilite_responsable='tst_responsabilite_responsable';
		/** [text] Structure */
		const tst_responsabilite_struct='tst_responsabilite_struct';
		/** [frame] Principales publications */
		const tst_f_publi_princi='tst_f_publi_princi';
		/** [file] Principales publications */
		const tst_publi_princi='tst_publi_princi';
		/** [tab] Informations administratives */
		const tst_t_infos_administratives='tst_t_infos_administratives';
		/** [frame] Adresse personnelle */
		const tst_f_adresseperso='tst_f_adresseperso';
		/** [text] Adresse (ligne 1) */
		const tst_adp_adresse1='tst_adp_adresse1';
		/** [text] Adresse (ligne 2) */
		const tst_adp_adresse2='tst_adp_adresse2';
		/** [text] Adresse (ligne 3) */
		const tst_adp_adresse3='tst_adp_adresse3';
		/** [text] Code postal */
		const tst_adp_codepostal='tst_adp_codepostal';
		/** [text] Ville */
		const tst_adp_localite='tst_adp_localite';
		/** [enum] Pays */
		const tst_adp_pays='tst_adp_pays';
		/** [array] Téléphone */
		const tst_adp_phone_array='tst_adp_phone_array';
		/** [enum] Type */
		const tst_adp_phone_type='tst_adp_phone_type';
		/** [text] Numéro */
		const tst_adp_phone_num='tst_adp_phone_num';
		/** [frame] Administratif */
		const tst_administratif='tst_administratif';
		/** [text] Numéro de sécurité sociale */
		const tst_secusociale='tst_secusociale';
		/** [enum] Situation famille */
		const tst_situation_fam='tst_situation_fam';
		/** [frame] Justificatifs */
		const tst_f_financialinfo='tst_f_financialinfo';
		/** [file] RIB ou autre document bancaire officiel */
		const tst_finance_rib='tst_finance_rib';
		/** [enum] Communication du bulletin */
		const tst_use_bs='tst_use_bs';
		/** [file] Bulletin de salaire ou de pension */
		const tst_finance_bs='tst_finance_bs';
		/** [date] Mois et année du bulletin déposé */
		const tst_finance_bs_date='tst_finance_bs_date';
		/** [frame] Informations financières – Zone Euro */
		const tst_f_dombancaire='tst_f_dombancaire';
		/** [text] Domiciliation bancaire */
		const tst_ban_agence='tst_ban_agence';
		/** [text] Code établissement/Banque */
		const tst_ban_etablissement='tst_ban_etablissement';
		/** [text] Code Guichet */
		const tst_ban_guichet='tst_ban_guichet';
		/** [text] Numéro de compte */
		const tst_ban_numcompte='tst_ban_numcompte';
		/** [text] Clé numéro de compte */
		const tst_ban_clecompte='tst_ban_clecompte';
		/** [text] IBAN */
		const tst_ban_iban='tst_ban_iban';
		/** [text] BIC (Swift Code) */
		const tst_ban_bic='tst_ban_bic';
		/** [text] Titulaire */
		const tst_dest_nom='tst_dest_nom';
		/** [text] Adresse 1 */
		const tst_dest_adresse1='tst_dest_adresse1';
		/** [text] Adresse 2 */
		const tst_dest_adresse2='tst_dest_adresse2';
		/** [text] Adresse 3 */
		const tst_dest_adresse3='tst_dest_adresse3';
		/** [text] Code postal */
		const tst_dest_codep='tst_dest_codep';
		/** [text] Ville */
		const tst_dest_ville='tst_dest_ville';
		/** [enum] Pays */
		const tst_dest_pays='tst_dest_pays';
		/** [text] Libellé du compte */
		const tst_ban_libcompte='tst_ban_libcompte';
		/** [frame] Informations financières – Hors Zone Euro */
		const tst_f_strangers='tst_f_strangers';
		/** [text] Etablissement code */
		const tst_st_etablissment='tst_st_etablissment';
		/** [text] Bank address */
		const tst_st_guichet='tst_st_guichet';
		/** [text] Account number */
		const tst_st_numcompte='tst_st_numcompte';
		/** [text] Rounting number / ABA / Primary account */
		const tst_st_clecompte='tst_st_clecompte';
		/** [text] Bic (Swift Code) */
		const tst_st_bic='tst_st_bic';
		/** [frame] Informations de Voyage : Abonnements & Cartes */
		const tst_f_voyages='tst_f_voyages';
		/** [array] Cartes de fidélité */
		const tst_l_reduc='tst_l_reduc';
		/** [enum] Type de carte */
		const tst_red_type='tst_red_type';
		/** [text] N° Carte */
		const tst_red_ref='tst_red_ref';
		/** [date] Date de validité */
		const tst_red_date_fin='tst_red_date_fin';
		/** [file] Carte grise */
		const tst_pj_carte_grise='tst_pj_carte_grise';
		/** [file] Police d'assurance */
		const tst_pj_assurance='tst_pj_assurance';
		/** [array] Autres fichiers */
		const tst_l_files='tst_l_files';
		/** [text] Type */
		const tst_pj_label='tst_pj_label';
		/** [file] Fichier */
		const tst_pj_file='tst_pj_file';
	}
}
