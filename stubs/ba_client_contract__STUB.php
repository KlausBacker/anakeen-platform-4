<?php
namespace Dcp\Family {
	/** Contrat client  */
	class Ba_client_contract extends \Sample\BusinessApp\Client { const familyName="BA_CLIENT_CONTRACT";}
}

namespace Dcp\AttributeIdentifiers {
	/** Contrat client  */
	class Ba_client_contract {
		/** [frame] Identification */
		const cli_fr_ident='cli_fr_ident';
		/** [text] Société */
		const cli_name='cli_name';
		/** [longtext] Adresse */
		const cli_addr='cli_addr';
		/** [text] Téléphone */
		const cli_phone='cli_phone';
		/** [date] Date de début de contrat */
		const cli_beg_date='cli_beg_date';
		/** [date] Date de fin de contrat */
		const cli_end_date='cli_end_date';
		/** [tab] Dépenses */
		const cli_tab_expensives='cli_tab_expensives';
		/** [frame] Dépenses */
		const cli_f_expensives='cli_f_expensives';
		/** [array] Tableau des dépenses */
		const cli_t_expensives='cli_t_expensives';
		/** [date] Date */
		const cli_exp_date='cli_exp_date';
		/** [docid("BA_RH_DIR")] Personne */
		const cli_exp_person='cli_exp_person';
		/** [money] Montant HT */
		const cli_exp_ht='cli_exp_ht';
		/** [money] Montant TTC */
		const cli_exp_ttc='cli_exp_ttc';
		/** [tab] Missions */
		const cli_tab_mission='cli_tab_mission';
		/** [frame] Ordre de missions */
		const cli_fr_mission='cli_fr_mission';
		/** [array] Ordre de missions */
		const cli_t_mission='cli_t_mission';
		/** [date] Date */
		const cli_mis_date='cli_mis_date';
		/** [docid("BA_RH_DIR")] Personne */
		const cli_mis_person='cli_mis_person';
		/** [text] Projet */
		const cli_mis_project='cli_mis_project';
		/** [tab] Fichiers */
		const cli_tab_files='cli_tab_files';
		/** [frame] Fichiers */
		const cli_f_files='cli_f_files';
		/** [array] Fichiers */
		const cli_t_files='cli_t_files';
		/** [file] Fichier */
		const cli_file_contract='cli_file_contract';
	}
}
