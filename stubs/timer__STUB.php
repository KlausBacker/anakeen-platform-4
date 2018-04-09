<?php
namespace Dcp\Family {
	/** Minuteur  */
	class Timer extends \Dcp\Core\Timer { const familyName="TIMER";}
}

namespace SmartStructure\Attributes {
	/** Minuteur  */
	class Timer {
		/** [frame] Identification */
		const tm_fr_ident='tm_fr_ident';
		/** [text] Titre */
		const tm_title='tm_title';
		/** [docid("x")] Famille */
		const tm_family='tm_family';
		/** [docid("x")] Famille cycle */
		const tm_workflow='tm_workflow';
		/** [text] Date de référence */
		const tm_dyndate='tm_dyndate';
		/** [double] Décalage (en jours) */
		const tm_refdaydelta='tm_refdaydelta';
		/** [double] Décalage (en heures) */
		const tm_refhourdelta='tm_refhourdelta';
		/** [array] Configuration */
		const tm_t_config='tm_t_config';
		/** [double] Délai (en jours) */
		const tm_delay='tm_delay';
		/** [double] Délai (en heures) */
		const tm_hdelay='tm_hdelay';
		/** [int] Nombre d'itérations */
		const tm_iteration='tm_iteration';
		/** [docid("MAILTEMPLATE")] Modèle de mail */
		const tm_tmail='tm_tmail';
		/** [text] Nouvel état */
		const tm_state='tm_state';
		/** [text] Méthode */
		const tm_method='tm_method';
	}
}
