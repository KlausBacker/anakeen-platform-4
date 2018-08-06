<?php

namespace SmartStructure {

    class Igroup extends \Anakeen\SmartStructures\Igroup\IGroupHooks
    {
        const familyName = "IGROUP";
    }
}

namespace SmartStructure\Fields {

    class Igroup
    {
        /**
        * Système
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> None </li>
        * </ul>
        */ 
        const grp_fr_intranet='grp_fr_intranet';
        /**
        * identifiant
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const us_login='us_login';
        /**
        * identifiant système
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const us_whatid='us_whatid';
        /**
        * groupe id
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const us_meid='us_meid';
        /**
        * Rôles associés
        * <ul>
        * <li> <i>access</i> None </li>
        * <li> <i>relation</i> ROLE </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const grp_roles='grp_roles';

    }
}