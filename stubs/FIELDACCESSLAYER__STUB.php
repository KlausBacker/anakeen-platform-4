<?php

namespace SmartStructure {

    class Fieldaccesslayer extends \Anakeen\SmartStructures\FieldAccessLayer\FieldAccessLayerHooks
    {
        const familyName = "FIELDACCESSLAYER";
    }
}

namespace SmartStructure\Fields {

    class Fieldaccesslayer
    {
        /**
        * Properties
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const fal_fr_ident='fal_fr_ident';
        /**
        * Title
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const fal_title='fal_title';
        /**
        * Description
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const fal_desc='fal_desc';
        /**
        * Structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const fal_famid='fal_famid';
        /**
        * Fields
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const fal_t_fields='fal_t_fields';
        /**
        * Field id
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const fal_fieldid='fal_fieldid';
        /**
        * New Access
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> FAL-Access </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const fal_fieldaccess='fal_fieldaccess';

    }
}