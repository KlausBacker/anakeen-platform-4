<?php
namespace SmartStructure {
	/** Contrôle de vues  */
	class Cvdoc extends \Anakeen\SmartStructures\Cvdoc\CVDocHooks { const familyName="CVDOC";}
}

namespace SmartStructure\Attributes {
	/** Contrôle de vues  */
	class Cvdoc extends Base {
		/** [longtext] Description */
		const cv_desc='cv_desc';
		/** [docid] Famille (id) */
		const cv_famid='cv_famid';
		/** [text] Famille */
		const cv_fam='cv_fam';
		/** [array] Vues */
		const cv_t_views='cv_t_views';
		/** [text] Identifiant de la vue */
		const cv_idview='cv_idview';
		/** [text] Label */
		const cv_lview='cv_lview';
		/** [enum] Type */
		const cv_kview='cv_kview';
		/** [text] Classe de configuration de rendu (HTML5) */
		const cv_renderconfigclass='cv_renderconfigclass';
		/** [docid("MASK")] Masque */
		const cv_mskid='cv_mskid';
		/** [int] Ordre de sélection */
		const cv_order='cv_order';
		/** [enum] Affichable */
		const cv_displayed='cv_displayed';
		/** [text] Menu */
		const cv_menu='cv_menu';
		/** [frame] Vues par défauts */
		const cv_fr_default='cv_fr_default';
		/** [text] Id création vues par défaut */
		const cv_idcview='cv_idcview';
		/** [text] Création vue */
		const cv_lcview='cv_lcview';
		/** [text] Classe d'accès au rendu (HTML5) */
		const cv_renderaccessclass='cv_renderaccessclass';
		/** [frame] Profil dynamique */
		const dpdoc_fr_dyn='dpdoc_fr_dyn';
		/** [docid("-1")] Famille pour le profil */
		const dpdoc_famid='dpdoc_famid';
		/** [text] Famille pour le profil (titre) */
		const dpdoc_fam='dpdoc_fam';
	}
}
