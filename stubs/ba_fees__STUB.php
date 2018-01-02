<?php
namespace Dcp\Family {
	/** Notes de frais  */
	class Ba_fees extends \Sample\BusinessApp\Fees { const familyName="BA_FEES";}
	/** Cycle Notes de frais  */
	class Ba_wfees extends \Sample\BusinessApp\WFees { const familyName="BA_WFEES";}
}

namespace Dcp\AttributeIdentifiers {
	/** Notes de frais  */
	class Ba_fees {
		/** [frame] Informations */
		const fee_fr_info='fee_fr_info';
		/** [docid("BA_RH_DIR")] Bénéficiaire */
		const fee_person='fee_person';
		/** [account] Compte utilisateur */
		const fee_account='fee_account';
		/** [date] Période */
		const fee_period='fee_period';
		/** [money] Avance */
		const fee_advance='fee_advance';
		/** [money] Total des dépenses */
		const fee_total='fee_total';
		/** [docid("BA_RH_DIR")] Signature du bénéficiaire */
		const fee_usersign='fee_usersign';
		/** [docid("BA_RH_DIR")] Signature du directeur */
		const fee_dirsign='fee_dirsign';
		/** [tab] Dépenses */
		const fee_tab_all_exp='fee_tab_all_exp';
		/** [frame] Tableau des dépenses */
		const fee_fr_all_exp='fee_fr_all_exp';
		/** [array] Tableau des dépenses */
		const fee_t_all_exp='fee_t_all_exp';
		/** [date] Date */
		const fee_exp_date='fee_exp_date';
		/** [docid("BA_CLIENT_CONTRACT")] Client */
		const fee_exp_client='fee_exp_client';
		/** [enum] Catégorie */
		const fee_exp_category='fee_exp_category';
		/** [docid("BA_PROVIDER_CONTRACT")] Fournisseur */
		const fee_exp_provider='fee_exp_provider';
		/** [text] Nature */
		const fee_exp_nature='fee_exp_nature';
		/** [money] Montant TTC */
		const fee_exp_tax='fee_exp_tax';
		/** [image] Fichier Justificatif */
		const fee_exp_file='fee_exp_file';
		/** [longtext] Hash */
		const fee_exp_file_hash='fee_exp_file_hash';
		/** [longtext] Stamp ID */
		const fee_exp_file_stampid='fee_exp_file_stampid';
		/** [date] Stamp Date */
		const fee_exp_file_stamp_date='fee_exp_file_stamp_date';
		/** [double] Latitude */
		const fee_exp_file_lat='fee_exp_file_lat';
		/** [double] Longitude */
		const fee_exp_file_lng='fee_exp_file_lng';
		/** [text] Dépassement de dépense */
		const fee_exp_exceed='fee_exp_exceed';
		/** [tab] Visualisation de chaque dépense */
		const fee_tab_viz='fee_tab_viz';
		/** [frame] Visualisation de chaque dépense */
		const fee_fr_viz='fee_fr_viz';
		/** [text]  */
		const fee_viz_title='fee_viz_title';
		/** [frame] Note de frais */
		const fee_fr_pdffile='fee_fr_pdffile';
		/** [file] Fichier ODT */
		const fee_odtfile='fee_odtfile';
		/** [file] Fichier PDF */
		const fee_pdffile='fee_pdffile';
		/** [frame] Preview */
		const fee_fr_preview='fee_fr_preview';
		/** [file] Preview template */
		const fee_preview_template='fee_preview_template';
		/** [docid(""BA_CATEGORIES"")] Dépenses Maximales */
		const fee_limit_values='fee_limit_values';
	}
	/** Cycle Notes de frais  */
	class Ba_wfees extends Wdoc {
		/** [frame] Asks */
		const wfee_fr_ask='wfee_fr_ask';
		/** [enum] Asks */
		const wfee_user_valid='wfee_user_valid';
		/** [enum] Autorisez-vous la demande de dépassement ? */
		const wfee_exceed_decision='wfee_exceed_decision';
	}
}
