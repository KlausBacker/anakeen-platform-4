<?php

namespace SmartStructure {

    class Adminparametershubconfiguration extends \Anakeen\AdminCenter\SmartStructures\AdminParametersHubConfiguration\AdminParametersHubConfigurationBehavior
    {
        const familyName = "ADMINPARAMETERSHUBCONFIGURATION";
    }
}

namespace SmartStructure\Fields {

    class Adminparametershubconfiguration extends Hubconfigurationgeneric
    {
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
        const admin_hub_configuration_options='admin_hub_configuration_options';
        /**
        * Global parameters
        * <ul>
        * <li> <i>relation</i> Admin_Param_TrueFalse </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const admin_hub_configuration_global='admin_hub_configuration_global';
        /**
        * User parameters
        * <ul>
        * <li> <i>relation</i> Admin_Param_TrueFalse </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const admin_hub_configuration_user='admin_hub_configuration_user';
        /**
        * Namespace
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>relation</i> Admin_Param_NameSpace </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const admin_hub_configuration_namespace='admin_hub_configuration_namespace';
        /**
        * Specific user
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const admin_hub_configuration_account='admin_hub_configuration_account';
        /**
        * Material-icon
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const admin_hub_configuration_icon='admin_hub_configuration_icon';
        /**
        * Label
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const admin_hub_configuration_label='admin_hub_configuration_label';

    }
}