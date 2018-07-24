<?php
namespace SmartStructure {
	/** Groupe d'utilisateurs  */
	class Igroup extends \Anakeen\SmartStructures\Igroup\IGroupHooks { const familyName="IGROUP";}
}

namespace SmartStructure\Fields {
	/** Groupe d'utilisateurs  */
	class Igroup extends Group {
		/** [frame] Système */
		const grp_fr_intranet='grp_fr_intranet';
		/** [text] identifiant */
		const us_login='us_login';
		/** [int] identifiant système */
		const us_whatid='us_whatid';
		/** [account] groupe id */
		const us_meid='us_meid';
		/** [docid("ROLE")] Rôles associés */
		const grp_roles='grp_roles';
		/** [menu] Modifier la hiérarchie */
		const grp_choosegroup='grp_choosegroup';
	}
}
