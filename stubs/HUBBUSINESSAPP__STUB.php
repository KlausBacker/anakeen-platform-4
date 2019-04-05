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
        * Options
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hba_options='hba_options';
        /**
        * Welcome tab
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> HBA_YES_NO_ENUM </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hba_welcome_option='hba_welcome_option';
        /**
        * Business App structure creation
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

    }
}