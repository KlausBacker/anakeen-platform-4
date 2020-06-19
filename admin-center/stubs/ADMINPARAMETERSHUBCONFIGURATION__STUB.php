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
        * Paramètres généraux
        * <ul>
        * <li> <i>relation</i> Hub_YesNo </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const admin_hub_configuration_global='admin_hub_configuration_global';
        /**
        * Paramètres utilisateurs
        * <ul>
        * <li> <i>relation</i> Hub_YesNo </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const admin_hub_configuration_user='admin_hub_configuration_user';
        /**
        * Utilisateur spécifique
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const admin_hub_configuration_account='admin_hub_configuration_account';
        /**
        * Namespace
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const admin_hub_configuration_namespace='admin_hub_configuration_namespace';

    }
}