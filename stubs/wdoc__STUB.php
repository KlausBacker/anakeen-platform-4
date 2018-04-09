<?php
namespace SmartStructure {
	/** Cycle de vie  */
	class Wdoc extends \Anakeen\SmartStructures\Wdoc\WDocHooks { const familyName="WDOC";}
}

namespace SmartStructure\Attributes {
	/** Cycle de vie  */
	class Wdoc extends Base {
		/** [longtext] description */
		const wf_desc='wf_desc';
		/** [menu] Initialisation */
		const wf_init='wf_init';
		/** [docid("-1")] Famille */
		const wf_famid='wf_famid';
		/** [text] Famille (titre) */
		const wf_fam='wf_fam';
		/** [frame] Profil dynamique */
		const dpdoc_fr_dyn='dpdoc_fr_dyn';
		/** [docid("-1")] Famille */
		const dpdoc_famid='dpdoc_famid';
		/** [text] Famille (titre) */
		const dpdoc_fam='dpdoc_fam';
		/** [tab] Étapes */
		const wf_tab_states='wf_tab_states';
		/** [tab] Transitions */
		const wf_tab_transitions='wf_tab_transitions';
	}
}
