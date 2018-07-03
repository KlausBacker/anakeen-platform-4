<?php
namespace SmartStructure {
	/** utilisateur  */
	class Iuser extends \Anakeen\SmartStructures\Iuser\IUserHooks { const familyName="IUSER";}
}

namespace SmartStructure\Fields {
	/** utilisateur  */
	class Iuser {
		/** [frame] État civil */
		const us_fr_ident='us_fr_ident';
		/** [text] nom */
		const us_lname='us_lname';
		/** [text] prénom */
		const us_fname='us_fname';
		/** [text] mail */
		const us_mail='us_mail';
		/** [text] mail principal */
		const us_extmail='us_extmail';
		/** [tab] Système */
		const us_tab_system='us_tab_system';
		/** [frame] Identification intranet */
		const us_fr_intranet='us_fr_intranet';
		/** [account] utilisateur id */
		const us_meid='us_meid';
		/** [text] login */
		const us_login='us_login';
		/** [text] identifiant */
		const us_whatid='us_whatid';
		/** [array] Rôles */
		const us_t_roles='us_t_roles';
		/** [account] Rôle */
		const us_roles='us_roles';
		/** [enum] Origine */
		const us_rolesorigin='us_rolesorigin';
		/** [account] Groupe */
		const us_rolegorigin='us_rolegorigin';
		/** [array] groupes d'appartenance */
		const us_groups='us_groups';
		/** [text] groupe (titre) */
		const us_group='us_group';
		/** [account] Groupe */
		const us_idgroup='us_idgroup';
		/** [int] date d'expiration epoch */
		const us_expires='us_expires';
		/** [int] délai d'expiration en jours */
		const us_daydelay='us_daydelay';
		/** [date] date d'expiration */
		const us_expiresd='us_expiresd';
		/** [time] heure d'expiration */
		const us_expirest='us_expirest';
		/** [int] délai d'expiration epoch */
		const us_passdelay='us_passdelay';
		/** [text] login LDAP */
		const us_ldapdn='us_ldapdn';
		/** [frame] Suppléants */
		const us_fr_substitute='us_fr_substitute';
		/** [account] Suppléant */
		const us_substitute='us_substitute';
		/** [account] Titulaires */
		const us_incumbents='us_incumbents';
		/** [frame] Mot de passe */
		const us_fr_userchange='us_fr_userchange';
		/** [password] nouveau mot de passe */
		const us_passwd1='us_passwd1';
		/** [password] confirmation mot de passe */
		const us_passwd2='us_passwd2';
		/** [frame] Paramètre */
		const us_fr_default='us_fr_default';
		/** [account] Groupe par défaut */
		const us_defaultgroup='us_defaultgroup';
		/** [frame] Sécurité */
		const us_fr_security='us_fr_security';
		/** [enum] état du compte */
		const us_status='us_status';
		/** [int] échecs de connexion */
		const us_loginfailure='us_loginfailure';
		/** [date] Date d'expiration du compte */
		const us_accexpiredate='us_accexpiredate';
		/** [menu] Réinitialiser échecs de connexion */
		const us_menuresetlogfails='us_menuresetlogfails';
		/** [menu] Activer le compte */
		const us_activateaccount='us_activateaccount';
		/** [menu] Désactiver le compte */
		const us_desactivateaccount='us_desactivateaccount';
		/** [frame] confidentialité */
		const us_fr_privacy='us_fr_privacy';
		/** [menu] Actualiser les utilisateurs */
		const us_inituser='us_inituser';
	}
}
