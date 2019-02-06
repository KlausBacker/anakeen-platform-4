<?php

namespace SmartStructure {

    class Hubconfiguration extends \Anakeen\Hub\SmartStructures\HubConfiguration\HubConfigurationBehavior
    {
        const familyName = "HUBCONFIGURATION";
    }
}

namespace SmartStructure\Fields {

    class Hubconfiguration
    {
        /**
        * Configuration
        * <ul>
        * <li> <i>type</i> tab </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_config='hub_config';
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_station_id_frame='hub_station_id_frame';
        /**
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const hub_station_id='hub_station_id';
        /**
        * Label
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_label_frame='hub_label_frame';
        /**
        * Element Titles
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_titles='hub_titles';
        /**
        * Title
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_title='hub_title';
        /**
        * Language
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_language='hub_language';
        /**
        * Icon
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_icon_frame='hub_icon_frame';
        /**
        * Mode
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> Icon_mode_enum </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hub_icon_enum='hub_icon_enum';
        /**
        * Icon
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_final_icon='hub_final_icon';
        /**
        * Icon picker
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_icon_font='hub_icon_font';
        /**
        * Image
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> image </li>
        * </ul>
        */ 
        const hub_icon_image='hub_icon_image';
        /**
        * Html icon
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const hub_icon_text='hub_icon_text';
        /**
        * Element location
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_slot_parameters='hub_slot_parameters';
        /**
        * Order in hub
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const hub_order='hub_order';
        /**
        * Dock position
        * <ul>
        * <li> <i>relation</i> Docker_pos_enum </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hub_docker_position='hub_docker_position';
        /**
        * Element activation
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_activated_frame='hub_activated_frame';
        /**
        * Element is activated
        * <ul>
        * <li> <i>relation</i> Yes_no_enum </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hub_activated='hub_activated';
        /**
        * Element is selectable
        * <ul>
        * <li> <i>relation</i> Yes_no_enum </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hub_selectable='hub_selectable';
        /**
        * Priority
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const hub_activated_order='hub_activated_order';
        /**
        * Element parameters
        * <ul>
        * <li> <i>type</i> tab </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_component_tab='hub_component_tab';
        /**
        * Parameters
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_component_parameters='hub_component_parameters';
        /**
        * Security
        * <ul>
        * <li> <i>type</i> tab </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_security='hub_security';
        /**
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_security_frame='hub_security_frame';
        /**
        * Roles
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_roles='hub_roles';
        /**
        * Role
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const hub_role='hub_role';

    }
}