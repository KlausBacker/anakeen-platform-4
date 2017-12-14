<?php
namespace Dcp\Family {
	/** Contrat Fournisseur  */
	class Ba_provider_contract extends \Sample\BusinessApp\Provider { const familyName="BA_PROVIDER_CONTRACT";}
}

namespace Dcp\AttributeIdentifiers {
	/** Contrat Fournisseur  */
	class Ba_provider_contract {
		/** [frame] Identification */
		const pro_fr_ident='pro_fr_ident';
		/** [text] Société */
		const pro_name='pro_name';
		/** [longtext] Adresse */
		const pro_addr='pro_addr';
		/** [text] Téléphone */
		const pro_phone='pro_phone';
		/** [date] Date de début de contrat */
		const pro_beg_date='pro_beg_date';
		/** [date] Date de fin de contrat */
		const pro_end_date='pro_end_date';
		/** [tab] Dépenses */
		const pro_tab_expensives='pro_tab_expensives';
		/** [frame] Dépenses */
		const pro_f_expensives='pro_f_expensives';
		/** [array] Tableau des dépenses */
		const pro_t_expensives='pro_t_expensives';
		/** [date] Date */
		const pro_exp_date='pro_exp_date';
		/** [docid("BA_RH_DIR")] Personne */
		const pro_exp_person='pro_exp_person';
		/** [money] Montant HT */
		const pro_exp_ht='pro_exp_ht';
		/** [money] Montant TTC */
		const pro_exp_ttc='pro_exp_ttc';
		/** [tab] Fichiers */
		const pro_tab_files='pro_tab_files';
		/** [frame] Fichiers */
		const pro_f_files='pro_f_files';
		/** [array] Fichiers */
		const pro_t_files='pro_t_files';
		/** [file] Fichier */
		const pro_file_contract='pro_file_contract';
	}
}
