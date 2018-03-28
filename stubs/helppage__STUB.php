<?php
namespace Dcp\Family {
	/** Aide en ligne  */
	class Helppage extends \Dcp\Core\HelpPage { const familyName="HELPPAGE";}
}

namespace SmartStructure\Attributes {
	/** Aide en ligne  */
	class Helppage {
		/** [frame] Aide */
		const help_fr_identification='help_fr_identification';
		/** [docid("x")] Famille */
		const help_family='help_family';
		/** [array] Description */
		const help_t_help='help_t_help';
		/** [text] Libellé */
		const help_name='help_name';
		/** [enum] Langue du libellé */
		const help_lang='help_lang';
		/** [longtext] Description */
		const help_description='help_description';
		/** [array] Rubriques */
		const help_t_sections='help_t_sections';
		/** [int] Ordre de la rubrique */
		const help_sec_order='help_sec_order';
		/** [text] Nom de la rubrique */
		const help_sec_name='help_sec_name';
		/** [enum] Langue */
		const help_sec_lang='help_sec_lang';
		/** [text] Clé de la rubrique */
		const help_sec_key='help_sec_key';
		/** [htmltext] Texte */
		const help_sec_text='help_sec_text';
		/** [frame] Paramètres de famille */
		const help_fr_family='help_fr_family';
		/** [array] Langues */
		const help_t_family='help_t_family';
		/** [text] Libellé de la langue */
		const help_p_lang_name='help_p_lang_name';
		/** [text] Langue */
		const help_p_lang_key='help_p_lang_key';
	}
}
