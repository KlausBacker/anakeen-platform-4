<?php
namespace Dcp\Family {
	/** Test tout type  */
	class Tst_ddui_alltype extends \Dcp\Test\Ddui\AllType { const familyName="TST_DDUI_ALLTYPE";}
}

namespace Dcp\AttributeIdentifiers {
	/** Test tout type  */
	class Tst_ddui_alltype {
		/** [frame] Titre */
		const test_ddui_all__f_title='test_ddui_all__f_title';
		/** [text ] Le titre */
		const test_ddui_all__title='test_ddui_all__title';
		/** [tab] Basiques */
		const test_ddui_all__t_tab='test_ddui_all__t_tab';
		/** [frame] Relations */
		const test_ddui_all__fr_rels='test_ddui_all__fr_rels';
		/** [account] Un compte */
		const test_ddui_all__account='test_ddui_all__account';
		/** [account] Des comptes */
		const test_ddui_all__account_multiple='test_ddui_all__account_multiple';
		/** [docid("TST_DDUI_ALLTYPE")] Un document */
		const test_ddui_all__docid='test_ddui_all__docid';
		/** [docid("TST_DDUI_ALLTYPE")] Des documents */
		const test_ddui_all__docid_multiple='test_ddui_all__docid_multiple';
		/** [frame] Le temps */
		const test_ddui_all__fr_date='test_ddui_all__fr_date';
		/** [date ] Une date */
		const test_ddui_all__date='test_ddui_all__date';
		/** [time] Une heure */
		const test_ddui_all__time='test_ddui_all__time';
		/** [timestamp] Une date avec  une heure */
		const test_ddui_all__timestamp='test_ddui_all__timestamp';
		/** [frame] Les nombres */
		const test_ddui_all__fr_number='test_ddui_all__fr_number';
		/** [integer ] Un entier */
		const test_ddui_all__integer='test_ddui_all__integer';
		/** [double ] Un décimal */
		const test_ddui_all__double='test_ddui_all__double';
		/** [money] Un sous */
		const test_ddui_all__money='test_ddui_all__money';
		/** [frame] Divers */
		const test_ddui_all__fr_misc='test_ddui_all__fr_misc';
		/** [password] Un mot de passe */
		const test_ddui_all__password='test_ddui_all__password';
		/** [color ] Une couleur */
		const test_ddui_all__color='test_ddui_all__color';
		/** [frame] Fichiers & images */
		const test_ddui_all__fr_file='test_ddui_all__fr_file';
		/** [file ] Un fichier */
		const test_ddui_all__file='test_ddui_all__file';
		/** [image ] Une image */
		const test_ddui_all__image='test_ddui_all__image';
		/** [frame] Les textes */
		const test_ddui_all__fr_text='test_ddui_all__fr_text';
		/** [htmltext ] Un texte formaté */
		const test_ddui_all__htmltext='test_ddui_all__htmltext';
		/** [longtext ] Un texte multiligne */
		const test_ddui_all__longtext='test_ddui_all__longtext';
		/** [text ] Un texte simple */
		const test_ddui_all__text='test_ddui_all__text';
		/** [tab] Les énumérés */
		const test_ddui_all__t_tab_enums='test_ddui_all__t_tab_enums';
		/** [frame] Énumérés directs simple */
		const test_ddui_all__fr_enumsimple='test_ddui_all__fr_enumsimple';
		/** [enum ] Un énuméré liste */
		const test_ddui_all__enumlist='test_ddui_all__enumlist';
		/** [enum ] Un énuméré auto */
		const test_ddui_all__enumauto='test_ddui_all__enumauto';
		/** [enum ] Un énuméré vertical */
		const test_ddui_all__enumvertical='test_ddui_all__enumvertical';
		/** [enum ] Un énuméré horizontal */
		const test_ddui_all__enumhorizontal='test_ddui_all__enumhorizontal';
		/** [enum ] Un énuméré booléen */
		const test_ddui_all__enumbool='test_ddui_all__enumbool';
		/** [frame] Énumérés serveur simple */
		const test_ddui_all__fr_enumserversimple='test_ddui_all__fr_enumserversimple';
		/** [enum ] Un énuméré liste */
		const test_ddui_all__enumserverlist='test_ddui_all__enumserverlist';
		/** [enum ] Un énuméré auto */
		const test_ddui_all__enumserverauto='test_ddui_all__enumserverauto';
		/** [enum ] Un énuméré vertical */
		const test_ddui_all__enumserververtical='test_ddui_all__enumserververtical';
		/** [enum ] Un énuméré horizontal */
		const test_ddui_all__enumserverhorizontal='test_ddui_all__enumserverhorizontal';
		/** [enum ] Un énuméré booléen */
		const test_ddui_all__enumserverbool='test_ddui_all__enumserverbool';
		/** [frame] Énumérés directs multiple */
		const test_ddui_all__fr_enummultiple='test_ddui_all__fr_enummultiple';
		/** [enum ] Des énumérés liste */
		const test_ddui_all__enumslist='test_ddui_all__enumslist';
		/** [enum ] Des énumérés auto */
		const test_ddui_all__enumsauto='test_ddui_all__enumsauto';
		/** [enum ] Des énumérés vertical */
		const test_ddui_all__enumsvertical='test_ddui_all__enumsvertical';
		/** [enum ] Des énumérés horizontal */
		const test_ddui_all__enumshorizontal='test_ddui_all__enumshorizontal';
		/** [frame] Énumérés server multiple */
		const test_ddui_all__fr_enumservermultiple='test_ddui_all__fr_enumservermultiple';
		/** [enum ] Des énumérés liste */
		const test_ddui_all__enumsserverlist='test_ddui_all__enumsserverlist';
		/** [enum ] Des énumérés auto */
		const test_ddui_all__enumsserverauto='test_ddui_all__enumsserverauto';
		/** [enum ] Des énumérés vertical */
		const test_ddui_all__enumsserververtical='test_ddui_all__enumsserververtical';
		/** [enum ] Des énumérés horizontal */
		const test_ddui_all__enumsserverhorizontal='test_ddui_all__enumsserverhorizontal';
		/** [tab] Les dates */
		const test_ddui_all__t_tab_date='test_ddui_all__t_tab_date';
		/** [frame] Date, heures & date avec l'heure */
		const test_ddui_all__frame_date='test_ddui_all__frame_date';
		/** [array] Le temps */
		const test_ddui_all__array_dates='test_ddui_all__array_dates';
		/** [date ] Des dates */
		const test_ddui_all__date_array='test_ddui_all__date_array';
		/** [time] Des heures */
		const test_ddui_all__time_array='test_ddui_all__time_array';
		/** [timestamp] Des dates avec l'heure */
		const test_ddui_all__timestamp_array='test_ddui_all__timestamp_array';
		/** [tab] Les relations */
		const test_ddui_all__t_tab_relations='test_ddui_all__t_tab_relations';
		/** [frame] Relations à entretenir */
		const test_ddui_all__frame_relation='test_ddui_all__frame_relation';
		/** [array] Les documents */
		const test_ddui_all__array_docid='test_ddui_all__array_docid';
		/** [docid("TST_DDUI_ALLTYPE")] Des documents */
		const test_ddui_all__docid_array='test_ddui_all__docid_array';
		/** [docid("TST_DDUI_ALLTYPE")] Encore plus de documents */
		const test_ddui_all__docid_multiple_array='test_ddui_all__docid_multiple_array';
		/** [array] Les comptes */
		const test_ddui_all__array_account='test_ddui_all__array_account';
		/** [account] Des comptes */
		const test_ddui_all__account_array='test_ddui_all__account_array';
		/** [account] Encore plus de comptes */
		const test_ddui_all__account_multiple_array='test_ddui_all__account_multiple_array';
		/** [tab] Les nombres */
		const test_ddui_all__t_tab_numbers='test_ddui_all__t_tab_numbers';
		/** [frame] Entier, décimaux et monnaie */
		const test_ddui_all__frame_numbers='test_ddui_all__frame_numbers';
		/** [array] Quelques nombres */
		const test_ddui_all__array_numbers='test_ddui_all__array_numbers';
		/** [double ] Des décimaux */
		const test_ddui_all__double_array='test_ddui_all__double_array';
		/** [integer ] Des entiers */
		const test_ddui_all__integer_array='test_ddui_all__integer_array';
		/** [money] Des sous */
		const test_ddui_all__money_array='test_ddui_all__money_array';
		/** [tab] Divers */
		const test_ddui_all__t_tab_misc='test_ddui_all__t_tab_misc';
		/** [frame] Énuméré, couleur et mot de passe */
		const test_ddui_all__frame_misc='test_ddui_all__frame_misc';
		/** [array] Quelques diverses données */
		const test_ddui_all__array_misc='test_ddui_all__array_misc';
		/** [color ] Des couleurs */
		const test_ddui_all__color_array='test_ddui_all__color_array';
		/** [password] Des mots de passe */
		const test_ddui_all__password_array='test_ddui_all__password_array';
		/** [array] Quelques énumérés simple */
		const test_ddui_all__array_singleenum='test_ddui_all__array_singleenum';
		/** [enum ] Un énuméré liste */
		const test_ddui_all__enumlist_array='test_ddui_all__enumlist_array';
		/** [enum ] Un énuméré auto */
		const test_ddui_all__enumauto_array='test_ddui_all__enumauto_array';
		/** [enum ] Un énuméré vertical */
		const test_ddui_all__enumvertical_array='test_ddui_all__enumvertical_array';
		/** [enum ] Un énuméré horizontal */
		const test_ddui_all__enumhorizontal_array='test_ddui_all__enumhorizontal_array';
		/** [enum ] Un énuméré booléen */
		const test_ddui_all__enumbool_array='test_ddui_all__enumbool_array';
		/** [array] Quelques énumérés multiples */
		const test_ddui_all__array_multipleenum='test_ddui_all__array_multipleenum';
		/** [enum ] Des énumérés liste */
		const test_ddui_all__enumslist_array='test_ddui_all__enumslist_array';
		/** [enum ] Des énumérés auto */
		const test_ddui_all__enumsauto_array='test_ddui_all__enumsauto_array';
		/** [enum ] Des énumérés verticaux */
		const test_ddui_all__enumsvertical_array='test_ddui_all__enumsvertical_array';
		/** [enum ] Des énumérés horizontaux */
		const test_ddui_all__enumshorizontal_array='test_ddui_all__enumshorizontal_array';
		/** [tab] Les fichiers */
		const test_ddui_all__t_tab_files='test_ddui_all__t_tab_files';
		/** [frame] Fichiers & images */
		const test_ddui_all__frame_files='test_ddui_all__frame_files';
		/** [array] Quelques fichiers */
		const test_ddui_all__array_files='test_ddui_all__array_files';
		/** [file ] Des fichiers */
		const test_ddui_all__file_array='test_ddui_all__file_array';
		/** [image ] Des images */
		const test_ddui_all__image_array='test_ddui_all__image_array';
		/** [tab] Les textes  */
		const test_ddui_all__t_tab_texts='test_ddui_all__t_tab_texts';
		/** [frame] Les textes non formatés */
		const test_ddui_all__frame_texts='test_ddui_all__frame_texts';
		/** [array] Textes simples et multilignes */
		const test_ddui_all__array_texts='test_ddui_all__array_texts';
		/** [text ] Des textes */
		const test_ddui_all__text_array='test_ddui_all__text_array';
		/** [longtext ] Des textes multiligne */
		const test_ddui_all__longtext_array='test_ddui_all__longtext_array';
		/** [array] Les textes HTML */
		const test_ddui_all__array_html='test_ddui_all__array_html';
		/** [htmltext ] Des textes formatés */
		const test_ddui_all__htmltext_array='test_ddui_all__htmltext_array';
	}
}
