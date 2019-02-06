<?php

namespace SmartStructure {

    class Hubconfigurationlabel extends \Anakeen\Hub\SmartStructures\HubConfigurationLabel\HubConfigurationLabelBehavior
    {
        const familyName = "HUBCONFIGURATIONLABEL";
    }
}

namespace SmartStructure\Fields {

    class Hubconfigurationlabel extends Hubconfiguration
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * </ul>
        */ 
        const hub_component_parameters='hub_component_parameters';
        /**
        * Label
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const label='label';

    }
}