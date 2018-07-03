<?php
namespace SmartStructure {
	/** Masque de saisie  */
	class Mask extends \Anakeen\SmartStructures\Mask\Mask { const familyName="MASK";}
}

namespace SmartStructure\Fields {
	/** Masque de saisie  */
	class Mask extends Base {
		/** [frame] Famille */
		const msk_fr_rest='msk_fr_rest';
		/** [docid("FAMILIES")] Famille */
		const msk_famid='msk_famid';
		/** [text] Famille (titre) */
		const msk_fam='msk_fam';
		/** [array] Contenu */
		const msk_t_contain='msk_t_contain';
		/** [text] Attrid */
		const msk_attrids='msk_attrids';
		/** [text] Visibilité */
		const msk_visibilities='msk_visibilities';
		/** [text] Obligatoire */
		const msk_needeeds='msk_needeeds';
	}
}
