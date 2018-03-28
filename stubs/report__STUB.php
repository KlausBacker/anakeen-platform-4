<?php
namespace Dcp\Family {
	/** Rapport  */
	class Report extends \Dcp\Core\Report { const familyName="REPORT";}
}

namespace SmartStructure\Attributes {
	/** Rapport  */
	class Report extends Dsearch {
		/** [tab] Présentation */
		const rep_tab_presentation='rep_tab_presentation';
		/** [frame] Présentation */
		const rep_fr_presentation='rep_fr_presentation';
		/** [longtext] Description */
		const rep_caption='rep_caption';
		/** [text] Tri */
		const rep_sort='rep_sort';
		/** [text] Id tri */
		const rep_idsort='rep_idsort';
		/** [enum] Ordre */
		const rep_ordersort='rep_ordersort';
		/** [int] Limite */
		const rep_limit='rep_limit';
		/** [array] Colonnes */
		const rep_tcols='rep_tcols';
		/** [text] Label */
		const rep_lcols='rep_lcols';
		/** [text] Id colonnes */
		const rep_idcols='rep_idcols';
		/** [text] Option de présentation */
		const rep_displayoption='rep_displayoption';
		/** [color] Couleur */
		const rep_colors='rep_colors';
		/** [enum] Pied de tableau */
		const rep_foots='rep_foots';
		/** [enum] Style */
		const rep_style='rep_style';
		/** [color] Couleur entête */
		const rep_colorhf='rep_colorhf';
		/** [color] Couleur impaire */
		const rep_colorodd='rep_colorodd';
		/** [color] Couleur paire */
		const rep_coloreven='rep_coloreven';
		/** [menu] Export CSV */
		const rep_csv='rep_csv';
		/** [menu] Version imprimable */
		const rep_imp='rep_imp';
		/** [frame] Paramètres */
		const rep_fr_param='rep_fr_param';
		/** [htmltext] Texte à afficher pour les valeurs protégées */
		const rep_noaccesstext='rep_noaccesstext';
		/** [int] Limite d'affichage pour le nombre de rangées */
		const rep_maxdisplaylimit='rep_maxdisplaylimit';
	}
}
