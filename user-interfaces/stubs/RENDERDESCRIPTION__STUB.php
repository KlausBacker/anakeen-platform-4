<?php

namespace SmartStructure {

    class Renderdescription extends \Anakeen\SmartStructures\RenderDescription\RenderDescriptionHooks
    {
        const familyName = "RENDERDESCRIPTION";
    }
}

namespace SmartStructure\Fields {

    class Renderdescription
    {
        /**
        * Information
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const rd_fr_info='rd_fr_info';
        /**
        * Title
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const rd_title='rd_title';
        /**
        * Structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const rd_famid='rd_famid';
        /**
        * Example
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const rd_example='rd_example';
        /**
        * Use by default in mode
        * <ul>
        * <li> <i>relation</i> RENDERDESCRIPTION-mode </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const rd_mode='rd_mode';
        /**
        * Restrict to language
        * <ul>
        * <li> <i>relation</i> RENDERDESCRIPTION-lang </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const rd_lang='rd_lang';
        /**
        * Descriptions
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const rd_fr_descritions='rd_fr_descritions';
        /**
        * Fields
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const rd_t_fields='rd_t_fields';
        /**
        * Label
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const rd_fieldparentlabel='rd_fieldparentlabel';
        /**
        * Label
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const rd_fieldlabel='rd_fieldlabel';
        /**
        * Field
        * <ul>
        * <li> <i>needed</i> true </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const rd_field='rd_field';
        /**
        * Main description
        * <ul>
        * <li> <i>needed</i> true </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> htmltext </li>
        * </ul>
        */ 
        const rd_description='rd_description';
        /**
        * Secondary description
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> htmltext </li>
        * </ul>
        */ 
        const rd_subdescription='rd_subdescription';
        /**
        * Collapse
        * <ul>
        * <li> <i>relation</i> RENDERDESCRIPTION-collapse </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const rd_collapsable='rd_collapsable';
        /**
        * Placement
        * <ul>
        * <li> <i>relation</i> RENDERDESCRIPTION-placement </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const rd_placement='rd_placement';

    }
}