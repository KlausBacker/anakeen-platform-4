<?php
namespace SmartStructure {
	/** Groupe de personnes  */
	class Group extends \Dcp\Core\AccountCollection { const familyName="GROUP";}
}

namespace SmartStructure\Attributes {
	/** Groupe de personnes  */
	class Group extends Dir {
		/** [frame] Identification */
		const grp_fr_ident='grp_fr_ident';
		/** [text] nom */
		const grp_name='grp_name';
		/** [text] mail */
		const grp_mail='grp_mail';
		/** [enum] sans adresse mail de groupe */
		const grp_hasmail='grp_hasmail';
		/** [frame] Groupes */
		const grp_fr='grp_fr';
		/** [account] sous groupes */
		const grp_idgroup='grp_idgroup';
		/** [account] groupes parents */
		const grp_idpgroup='grp_idpgroup';
		/** [enum] est rafraîchi */
		const grp_isrefreshed='grp_isrefreshed';
		/** [menu] Gérer les membres */
		const grp_adduser='grp_adduser';
		/** [menu] Rafraîchir */
		const grp_refresh='grp_refresh';
		/** [frame] basique */
		const fr_basic='fr_basic';
		/** [text] titre */
		const ba_title='ba_title';
		/** [frame] Restrictions */
		const fld_fr_rest='fld_fr_rest';
		/** [frame] Profils par défaut */
		const fld_fr_prof='fld_fr_prof';
	}
}
