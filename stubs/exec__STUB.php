<?php
namespace SmartStructure {
	/** Processus  */
	class Exec extends \Anakeen\SmartStructures\Exec\ExecHooks { const familyName="EXEC";}
}

namespace SmartStructure\Fields {
	/** Processus  */
	class Exec {
		/** [frame] Identification */
		const exec_fr_ident='exec_fr_ident';
		/** [docid("IUSER")] Exécutant */
		const exec_iduser='exec_iduser';
		/** [text] Exécutant (titre) */
		const exec_user='exec_user';
		/** [docid("BATCH")] Issue de */
		const exec_idref='exec_idref';
		/** [text] Référent (titre) */
		const exec_ref='exec_ref';
		/** [text] Titre */
		const exec_title='exec_title';
		/** [enum] Exécution */
		const exec_status='exec_status';
		/** [timestamp] Exécution depuis */
		const exec_statusdate='exec_statusdate';
		/** [frame] Traitement */
		const exec_fr_batch='exec_fr_batch';
		/** [text] Application */
		const exec_application='exec_application';
		/** [text] Action */
		const exec_action='exec_action';
		/** [text] Api */
		const exec_api='exec_api';
		/** [array] Paramètres */
		const exec_t_parameters='exec_t_parameters';
		/** [text] Variable */
		const exec_idvar='exec_idvar';
		/** [text] Valeur */
		const exec_valuevar='exec_valuevar';
		/** [frame] Dates */
		const exec_fr_date='exec_fr_date';
		/** [timestamp] Précédente date d'exécution */
		const exec_prevdate='exec_prevdate';
		/** [timestamp] Prochaine date d'exécution */
		const exec_nextdate='exec_nextdate';
		/** [timestamp] À exécuter le */
		const exec_handnextdate='exec_handnextdate';
		/** [int] Période en jours */
		const exec_periodday='exec_periodday';
		/** [int] Période en heures */
		const exec_periodhour='exec_periodhour';
		/** [int] Période en minutes */
		const exec_periodmin='exec_periodmin';
		/** [timestamp] Jusqu'au */
		const exec_periodenddate='exec_periodenddate';
		/** [enum] Jour de la semaine */
		const exec_perioddaynumber='exec_perioddaynumber';
		/** [frame] Compte-rendu */
		const exec_fr_cr='exec_fr_cr';
		/** [timestamp("%A %d %B %Y %X")] Date d'exécution */
		const exec_date='exec_date';
		/** [time("%H:%M:%S")] Durée d'exécution */
		const exec_elapsed='exec_elapsed';
		/** [text] Statut */
		const exec_state='exec_state';
		/** [ifile] Détail */
		const exec_detail='exec_detail';
		/** [longtext] Log */
		const exec_detaillog='exec_detaillog';
		/** [menu] Exécuter maintenant */
		const exec_bgexec='exec_bgexec';
		/** [menu] Abandonner l'exécution en cours */
		const exec_reset='exec_reset';
		/** [frame] Paramètre */
		const exec_fr_param='exec_fr_param';
		/** [docid("IUSER")] Administrateur */
		const exec_idadmin='exec_idadmin';
	}
}
