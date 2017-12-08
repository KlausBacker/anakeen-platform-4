<?php
namespace Dcp\Family {
	/** Rôle  */
	class Role extends \Dcp\Core\RoleAccount { const familyName="ROLE";}
}

namespace Dcp\AttributeIdentifiers {
	/** Rôle  */
	class Role {
		/** [frame] Identification */
		const role_fr_ident='role_fr_ident';
		/** [text] Référence */
		const role_login='role_login';
		/** [text] Libellé */
		const role_name='role_name';
		/** [int] Identifiant système */
		const us_whatid='us_whatid';
	}
}
