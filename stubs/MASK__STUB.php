<?php

namespace SmartStructure {

    class Mask extends \Anakeen\SmartStructures\Mask\MaskHooks
    {
        const familyName = "MASK";
    }
}

namespace SmartStructure\Fields {

    class Mask
    {
        /**
        * Informtation
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const msk_fr_rest='msk_fr_rest';
        /**
        * Structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const msk_famid='msk_famid';
        /**
        * Fields
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const msk_t_contain='msk_t_contain';
        /**
        * Field
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const msk_attrids='msk_attrids';
        /**
        * Visibilité
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const msk_visibilities='msk_visibilities';
        /**
        * Obligatoire
        * <ul>
        * <li> <i>relation</i> MASK-needed </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const msk_needeeds='msk_needeeds';

    }
}