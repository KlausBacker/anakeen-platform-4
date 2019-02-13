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
        * Hub Station
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const hub_station_id='hub_station_id';
        /**
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_title='hub_title';
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
        * <li> <i>relation</i> Hub_DockerPos </li>
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
        * <li> <i>relation</i> Hub_YesNo </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hub_activated='hub_activated';
        /**
        * Element is selectable
        * <ul>
        * <li> <i>relation</i> Hub_YesNo </li>
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
        /**
        * Hub asset parameters
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>kind</i> Parameter </li>
        * </ul>
        */ 
        const hub_fr_assets='hub_fr_assets';
        /**
        * Javascript assets
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>kind</i> Parameter </li>
        * </ul>
        */ 
        const hub_jsassets='hub_jsassets';
        /**
        * Javascript Url
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * <li> <i>kind</i> Parameter </li>
        * </ul>
        */ 
        const hub_jsasset='hub_jsasset';
        /**
        * CSS assets
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>kind</i> Parameter </li>
        * </ul>
        */ 
        const hub_cssassets='hub_cssassets';
        /**
        * Css Url
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * <li> <i>kind</i> Parameter </li>
        * </ul>
        */ 
        const hub_cssasset='hub_cssasset';

    }
}