<?php

namespace SmartStructure {

    class Hubinstanciation extends \Anakeen\Hub\SmartStructures\HubInstanciation\HubInstanciationBehavior
    {
        const familyName = "HUBINSTANCIATION";
    }
}

namespace SmartStructure\Fields {

    class Hubinstanciation
    {
        /**
        * Configuration
        * <ul>
        * <li> <i>type</i> tab </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_instance_config='hub_instance_config';
        /**
        * Information
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_instance='hub_instance';
        /**
        * Logical Name
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const instance_logical_name='instance_logical_name';
        /**
        * Fav icon
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> image </li>
        * </ul>
        */ 
        const hub_instanciation_icone='hub_instanciation_icone';
        /**
        * Label
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_instance_label_frame='hub_instance_label_frame';
        /**
        * Hub Station Titles
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_instance_titles='hub_instance_titles';
        /**
        * Title
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_instance_title='hub_instance_title';
        /**
        * Language
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_instance_language='hub_instance_language';
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
        * <li> <i>match</i> role </li>
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