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
        * Label component parameters
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const label_parameters='label_parameters';
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