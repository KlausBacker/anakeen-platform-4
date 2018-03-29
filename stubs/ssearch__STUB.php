<?php
namespace SmartStructure {
	/** Recherche spécialisée  */
	class Ssearch extends \Dcp\Core\SpecialSearch { const familyName="SSEARCH";}
}

namespace SmartStructure\Attributes {
	/** Recherche spécialisée  */
	class Ssearch extends Search {
		/** [frame] Fonction */
		const se_fr_function='se_fr_function';
		/** [text] Fichier PHP */
		const se_phpfile='se_phpfile';
		/** [text] Fonction PHP */
		const se_phpfunc='se_phpfunc';
		/** [text] Argument PHP */
		const se_phparg='se_phparg';
	}
}
