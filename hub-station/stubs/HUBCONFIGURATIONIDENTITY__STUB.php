<?php

namespace SmartStructure {

    class Hubconfigurationidentity extends \Anakeen\Hub\SmartStructures\HubConfigurationIdentity\HubConfigurationIdentityBehavior
    {
        const familyName = "HUBCONFIGURATIONIDENTITY";
    }
}

namespace SmartStructure\Fields {

    class Hubconfigurationidentity extends Hubconfigurationslot
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * </ul>
        */ 
        const hub_component_parameters='hub_component_parameters';
        /**
        * Email alterable
        * <ul>
        * <li> <i>relation</i> Hub_YesNo </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const email_alterable='email_alterable';
        /**
        * Password alterable
        * <ul>
        * <li> <i>relation</i> Hub_YesNo </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const password_alterable='password_alterable';

    }
}