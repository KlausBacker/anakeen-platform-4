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
        /**
        * Aide
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const help_fr_identification='help_fr_identification';
        /**
        * Structure
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const help_family='help_family';
        /**
        * Description
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const help_t_help='help_t_help';
        /**
        * Libellé
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const help_name='help_name';
        /**
        * Langue du libellé
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> helppage-help_lang </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const help_lang='help_lang';
        /**
        * Description
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const help_description='help_description';
        /**
        * Rubriques
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const help_t_sections='help_t_sections';
        /**
        * Ordre de la rubrique
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const help_sec_order='help_sec_order';
        /**
        * Nom de la rubrique
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const help_sec_name='help_sec_name';
        /**
        * Clé de la rubrique
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const help_sec_key='help_sec_key';
        /**
        * Langue
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> helppage-help_sec_lang </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const help_sec_lang='help_sec_lang';
        /**
        * Texte
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> htmltext </li>
        * </ul>
        */ 
        const help_sec_text='help_sec_text';

    }
}