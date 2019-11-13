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
        * <li> <i>relation</i> HUBINSTANCIATION </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const hub_station_id='hub_station_id';
        /**
        * Name
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_title='hub_title';
        /**
        * Dock
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_slot_parameters='hub_slot_parameters';
        /**
        * Order in dock
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
        * Hub element status
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_activated_frame='hub_activated_frame';
        /**
        * Element is default selected
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
        * Element is expandable
        * <ul>
        * <li> <i>relation</i> Hub_YesNo </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hub_expandable='hub_expandable';
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
        * Security Roles
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_security_frame='hub_security_frame';
        /**
        * Roles to display hub element
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>match</i> role </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const hub_visibility_roles='hub_visibility_roles';
        /**
        * Roles to access hub element API
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>match</i> role </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const hub_execution_roles='hub_execution_roles';
        /**
        * Security access
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * <li> <i>kind</i> Parameter </li>
        * </ul>
        */ 
        const hub_p_securityaccess='hub_p_securityaccess';
        /**
        * Mandatory route role
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * <li> <i>kind</i> Parameter </li>
        * </ul>
        */ 
        const hub_p_routes_role='hub_p_routes_role';

    }
}