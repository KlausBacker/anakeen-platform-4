<?php

namespace SmartStructure {

    class Hubconfiguration extends \Anakeen\SmartStructures\HubConfiguration\HubConfigurationSmartStructure
    {
        const familyName = "HubConfiguration";
    }
}

namespace SmartStructure\Fields {

    class Hubconfiguration
    {
        /**
        * Hub configuration
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hub_config='hub_config';
        /**
        * Titles
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
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_language_code='hub_language_code';
        /**
        * Roles
        * <ul>
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
        * Icon
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_icon='hub_icon';
        /**
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_font_name='hub_font_name';
        /**
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_icon_name='hub_icon_name';
        /**
        * Element id in the hub
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_id='hub_id';
        /**
        * Mono element
        * <ul>
        * <li> <i>relation</i> Yes_no_enum </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hub_mono_element='hub_mono_element';
        /**
        * Element activated
        * <ul>
        * <li> <i>relation</i> Yes_no_enum </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hub_activated='hub_activated';
        /**
        * Order in hub
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const hub_order='hub_order';

    }
}