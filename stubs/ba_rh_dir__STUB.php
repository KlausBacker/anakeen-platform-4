<?php
namespace Dcp\Family {
	/** Dossier RH  */
	class Ba_rh_dir extends \Sample\BusinessApp\RHDir { const familyName="BA_RH_DIR";}
}

namespace Dcp\AttributeIdentifiers {
	/** Dossier RH  */
	class Ba_rh_dir {
		/** [frame] Personne */
		const rh_fr_person='rh_fr_person';
		/** [text] Nom */
		const rh_person_lastname='rh_person_lastname';
		/** [text] Prénom */
		const rh_person_firstname='rh_person_firstname';
		/** [text] Emploi */
		const rh_person_job='rh_person_job';
		/** [date] Date de naissance */
		const rh_person_birthdate='rh_person_birthdate';
		/** [longtext] Adresse */
		const rh_person_address='rh_person_address';
		/** [text] Courriel */
		const rh_person_mail='rh_person_mail';
		/** [tab] Fichiers */
		const rh_tab_files='rh_tab_files';
		/** [frame] Fichiers */
		const rh_fr_files='rh_fr_files';
		/** [array] Fichiers */
		const rh_t_files='rh_t_files';
		/** [file] Fichier */
		const rh_file='rh_file';
		/** [tab] Bulletins de salaire */
		const rh_tab_pay='rh_tab_pay';
		/** [frame] Bulletins de salaire */
		const rh_fr_pay='rh_fr_pay';
		/** [array] Bulletins de salaire */
		const rh_t_pay='rh_t_pay';
		/** [file] Bulletin */
		const rh_pay='rh_pay';
		/** [tab] Informations financières */
		const rh_tab_financials='rh_tab_financials';
		/** [frame] Informations financières */
		const rh_fr_financials='rh_fr_financials';
		/** [array] Informations financières */
		const rh_t_financials='rh_t_financials';
		/** [text] Code Pays */
		const rh_fin_country='rh_fin_country';
		/** [text] Code de vérification */
		const rh_fin_check='rh_fin_check';
		/** [text] RIB (BBAN) */
		const rh_fin_rib='rh_fin_rib';
		/** [tab] Signature */
		const rh_tab_sign='rh_tab_sign';
		/** [frame] Signature */
		const rh_fr_sign='rh_fr_sign';
		/** [file] Certificat */
		const rh_sign_cert='rh_sign_cert';
		/** [image] Image Signature */
		const rh_sign_pict='rh_sign_pict';
	}
}
