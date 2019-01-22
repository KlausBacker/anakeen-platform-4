<?php

namespace SmartStructure {

    class Hubconfigurationlogout extends \Anakeen\Hub\SmartStructures\HubConfigurationLogout\HubConfigurationLogoutBehavior
    {
        const familyName = "HUBCONFIGURATIONLOGOUT";
    }
}

namespace SmartStructure\Fields {

    class Hubconfigurationlogout extends Hubconfigurationslot
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * </ul>
        */ 
        const hub_component_parameters='hub_component_parameters';
        /**
        * Title
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const logout_title='logout_title';

    }
}