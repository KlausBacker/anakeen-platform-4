<?php

namespace SmartStructure {

    class Wdoc extends \Anakeen\SmartStructures\Wdoc\WDocHooks
    {
        const familyName = "WDOC";
    }
}

namespace SmartStructure\Fields {

    class Wdoc
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * </ul>
        */ 
        const fr_basic='fr_basic';
        /**
        * description
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const wf_desc='wf_desc';
        /**
        * Structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const wf_famid='wf_famid';
        /**
        * Structure (titre)
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const wf_fam='wf_fam';
        /**
        * Profil dynamique
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const dpdoc_fr_dyn='dpdoc_fr_dyn';
        /**
        * Structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const dpdoc_famid='dpdoc_famid';
        /**
        * Structure (titre)
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const dpdoc_fam='dpdoc_fam';
        /**
        * Étapes
        * <ul>
        * <li> <i>type</i> tab </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const wf_tab_states='wf_tab_states';
        /**
        * Transitions
        * <ul>
        * <li> <i>type</i> tab </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const wf_tab_transitions='wf_tab_transitions';

    }
}