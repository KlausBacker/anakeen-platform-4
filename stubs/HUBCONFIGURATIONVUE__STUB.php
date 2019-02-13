<?php

namespace SmartStructure {

    class Hubconfigurationvue extends \Anakeen\Hub\SmartStructures\HubConfigurationVue\HubConfigurationVueBehavior
    {
        const familyName = "HUBCONFIGURATIONVUE";
    }
}

namespace SmartStructure\Fields {

    class Hubconfigurationvue extends Hubconfiguration
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * </ul>
        */ 
        const hub_slot_parameters='hub_slot_parameters';
        /**
        * Router entry
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hub_vue_router_entry='hub_vue_router_entry';

    }
}