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
        * icone
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
        const hub_language='hub_language';
        /**
        * Language code
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */
        const hub_language_code='hub_language_code';
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