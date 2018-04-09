<?php
namespace SmartStructure {
	/** Recherche détaillée  */
	class Dsearch extends \Anakeen\SmartStructures\Dsearch\DSearchHooks { const familyName="DSEARCH";}
}

namespace SmartStructure\Attributes {
	/** Recherche détaillée  */
	class Dsearch extends Search {
		/** [frame] Conditions */
		const se_fr_detail='se_fr_detail';
		/** [enum] Condition */
		const se_ol='se_ol';
		/** [array] Conditions */
		const se_t_detail='se_t_detail';
		/** [enum] Opérateur */
		const se_ols='se_ols';
		/** [enum] Parenthèse gauche */
		const se_leftp='se_leftp';
		/** [text] Attributs */
		const se_attrids='se_attrids';
		/** [text] Fonctions */
		const se_funcs='se_funcs';
		/** [text] Mot-clefs */
		const se_keys='se_keys';
		/** [enum] Parenthèse droite */
		const se_rightp='se_rightp';
		/** [array] Filtres */
		const se_t_filters='se_t_filters';
		/** [xml] Filtre */
		const se_filter='se_filter';
		/** [enum] Type */
		const se_typefilter='se_typefilter';
	}
}
