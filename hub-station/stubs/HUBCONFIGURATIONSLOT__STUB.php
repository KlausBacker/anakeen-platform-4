<?php

namespace SmartStructure {

    class Hubconfigurationslot extends \Anakeen\Hub\SmartStructures\HubConfigurationSlot\HubConfigurationSlotBehavior
    {
        const familyName = "HUBCONFIGURATIONSLOT";
    }
}

namespace SmartStructure\Fields {

    class Hubconfigurationslot extends Hubconfiguration
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * </ul>
        */ 
        const hub_slot_parameters='hub_slot_parameters';

    }
}