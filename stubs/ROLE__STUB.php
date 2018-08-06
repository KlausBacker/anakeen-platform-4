<?php

namespace SmartStructure {

    class Role extends \Anakeen\SmartStructures\Role\RoleHooks
    {
        const familyName = "ROLE";
    }
}

namespace SmartStructure\Fields {

    class Role
    {
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const role_fr_ident='role_fr_ident';
        /**
        * Référence
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const role_login='role_login';
        /**
        * Libellé
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const role_name='role_name';
        /**
        * Identifiant système
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const us_whatid='us_whatid';

    }
}