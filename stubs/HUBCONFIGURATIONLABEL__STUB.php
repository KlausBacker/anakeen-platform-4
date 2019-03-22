<?php

namespace SmartStructure {

    class Hubconfigurationlabel extends \Anakeen\Hub\SmartStructures\HubConfigurationLabel\HubConfigurationLabelBehavior
    {
        const familyName = "HUBCONFIGURATIONLABEL";
    }
}

namespace SmartStructure\Fields {

    class Hubconfigurationlabel extends Hubconfigurationslot
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * </ul>
        */ 
        const hub_component_parameters='hub_component_parameters';
        /**
        * Html label
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const label='label';
        /**
        * Extended Html label
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const extended_label='extended_label';

    }
}