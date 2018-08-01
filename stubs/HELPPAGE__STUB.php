<?php

namespace SmartStructure {

    class Helppage extends \Anakeen\SmartStructures\Helppage\HelpPageHooks
    {
        const familyName = "HELPPAGE";
    }
}

namespace SmartStructure\Fields {

    class Helppage
    {
        /** [array] Description */
        const help_t_help='help_t_help';
        /** [text] Libellé */
        const help_name='help_name';
        /** [longtext] Description */
        const help_description='help_description';
        /** [enum] Langue du libellé */
        const help_lang='help_lang';
        /** [array] Rubriques */
        const help_t_sections='help_t_sections';
        /** [text] Nom de la rubrique */
        const help_sec_name='help_sec_name';
        /** [text] Clé de la rubrique */
        const help_sec_key='help_sec_key';
        /** [enum] Langue */
        const help_sec_lang='help_sec_lang';
        /** [int] Ordre de la rubrique */
        const help_sec_order='help_sec_order';
        /** [docid("-1")] Structure */
        const help_family='help_family';

    }
}