<?php

namespace SmartStructure {

    class Hubbusinessapp extends \Anakeen\BusinessApp\SmartStructures\HubBusinessApp\HubBusinessAppBehavior
    {
        const familyName = "HUBBUSINESSAPP";
    }
}

namespace SmartStructure\Fields {

    class Hubbusinessapp extends Hubconfigurationvue
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * </ul>
        */ 
        const hub_component_parameters='hub_component_parameters';
        /**
        * Business App icon
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> image </li>
        * </ul>
        */ 
        const hba_icon_image='hba_icon_image';
        /**
        * Business App Titles
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hba_titles='hba_titles';
        /**
        * Title
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hba_title='hba_title';
        /**
        * Language
        * <ul>
        * <li> <i>relation</i> HBA_SUPPORT_LANG </li>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hba_language='hba_language';
        /**
        * Business App collections
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hba_collections='hba_collections';
        /**
        * Collection
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> DSEARCH </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const hba_collection='hba_collection';
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * </ul>
        */ 
        const hub_component_tab='hub_component_tab';
        /**
        * Welcome Tab options
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hba_options='hba_options';
        /**
        * Enable
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> HBA_YES_NO_ENUM </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hba_welcome_option='hba_welcome_option';
        /**
        * Title HTML template
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const hba_welcome_title='hba_welcome_title';
        /**
        * Smart Structure creation
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hba_structure_creation='hba_structure_creation';
        /**
        * Structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const hba_structure='hba_structure';
        /**
        * Grid collections
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hba_grid_collections='hba_grid_collections';
        /**
        * Grid collection
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> REPORT </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const hba_grid_collection='hba_grid_collection';

    }
}