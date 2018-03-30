<?php
namespace SmartStructure {
	/** Recherche groupée  */
	class Msearch extends \Anakeen\SmartStructures\Msearch\MSearchHooks { const familyName="MSEARCH";}
}

namespace SmartStructure\Attributes {
	/** Recherche groupée  */
	class Msearch extends Search {
		/** [frame] Critère */
		const se_crit='se_crit';
		/** [frame] Les recherches */
		const se_fr_searches='se_fr_searches';
		/** [array] Ensemble de recherche */
		const seg_t_cond='seg_t_cond';
		/** [docid("SEG_IDCOND")] Recherche */
		const seg_idcond='seg_idcond';
		/** [text] Recherche (titre) */
		const seg_cond='seg_cond';
	}
}
