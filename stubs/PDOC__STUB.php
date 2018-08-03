<?php

namespace SmartStructure {

    class Pdoc extends \Anakeen\SmartStructures\Profiles\PDocHooks
    {
        const familyName = "PDOC";
    }
}

namespace SmartStructure\Fields {

    class Pdoc
    {
        /**
        * Basique
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const fr_basic='fr_basic';
        /**
        * Titre
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const ba_title='ba_title';
        /**
        * Description
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const ba_desc='ba_desc';
        /**
        * Dynamique
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const dpdoc_fr_dyn='dpdoc_fr_dyn';
        /**
        * Smart Structure utilisable pour les droits en fonction des attributs "account"
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const dpdoc_famid='dpdoc_famid';
        /**
        * Smart Structure (titre)
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const dpdoc_fam='dpdoc_fam';

    }
}