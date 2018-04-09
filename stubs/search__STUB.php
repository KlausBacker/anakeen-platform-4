<?php
namespace SmartStructure {
	/** Recherche  */
	class Search extends \Anakeen\SmartStructures\Search\SearchHooks { const familyName="SEARCH";}
}

namespace SmartStructure\Attributes {
	/** Recherche  */
	class Search extends Base {
		/** [account] Auteur */
		const se_author='se_author';
		/** [color] Couleur intercalaire */
		const gui_color='gui_color';
		/** [enum] Utilisable comme flux RSS */
		const gui_isrss='gui_isrss';
		/** [enum] Flux RSS système */
		const gui_sysrss='gui_sysrss';
		/** [enum] À utiliser dans les menus */
		const se_memo='se_memo';
		/** [frame] Critère */
		const se_crit='se_crit';
		/** [text] Mot-clef */
		const se_key='se_key';
		/** [enum] Révision */
		const se_latest='se_latest';
		/** [enum] Mode  */
		const se_case='se_case';
		/** [text] Famille */
		const se_fam='se_fam';
		/** [docid] Famille (id) */
		const se_famid='se_famid';
		/** [enum] Inclure les documents système */
		const se_sysfam='se_sysfam';
		/** [docid] Dossier racine */
		const se_idfld='se_idfld';
		/** [text] À partir du dossier */
		const se_cfld='se_cfld';
		/** [enum] Dans la poubelle */
		const se_trash='se_trash';
		/** [docid("ARCHIVING")] Dans l'archive */
		const se_archive='se_archive';
		/** [int] Profondeur de recherche */
		const se_sublevel='se_sublevel';
		/** [text] Requête sql */
		const se_sqlselect='se_sqlselect';
		/** [docid] Id dossier père courant */
		const se_idcfld='se_idcfld';
		/** [text] Dossier père courant */
		const se_ccfld='se_ccfld';
		/** [text] Trié par */
		const se_orderby='se_orderby';
		/** [enum] Sans sous famille */
		const se_famonly='se_famonly';
		/** [enum] Document */
		const se_acl='se_acl';
		/** [text] Requête statique */
		const se_static='se_static';
		/** [menu] Ouvrir */
		const se_open='se_open';
		/** [menu] Ouvrir comme une chemise */
		const se_openfolio='se_openfolio';
		/** [menu] RSS visible/masquée aux utilisateurs */
		const se_setsysrss='se_setsysrss';
	}
}
