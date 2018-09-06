<?php

namespace SmartStructure {

    class Fieldaccesslayerlist extends \Anakeen\SmartStructures\FieldAccessLayerList\FieldAccessLayerListHooks
    {
        const familyName = "FIELDACCESSLAYERLIST";
    }
}

namespace SmartStructure\Fields {

    class Fieldaccesslayerlist
    {
        /**
        * Properties
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const fall_fr_ident='fall_fr_ident';
        /**
        * Title
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
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
        * Structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const fall_famid='fall_famid';
        /**
        * Access Layers
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const fall_t_layers='fall_t_layers';
        /**
        * Access name
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const fall_aclname='fall_aclname';
        /**
        * Layer
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> FIELDACCESSLAYER </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const fall_layer='fall_layer';
        /**
        * Profil dynamique
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const dpdoc_fr_dyn='dpdoc_fr_dyn';
        /**
        * Structure pour le profil
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const dpdoc_famid='dpdoc_famid';

    }
}