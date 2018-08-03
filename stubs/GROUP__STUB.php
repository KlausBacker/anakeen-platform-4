<?php

namespace SmartStructure {

    class Group extends \Anakeen\SmartStructures\Group\GroupHooks
    {
        const familyName = "GROUP";
    }
}

namespace SmartStructure\Fields {

    class Group
    {
        /**
        * basique
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> None </li>
        * </ul>
        */ 
        const fr_basic='fr_basic';
        /**
        * titre
        * <ul>
        * <li> <i>access</i> None </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const ba_title='ba_title';
        /**
        * Restrictions
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> None </li>
        * </ul>
        */ 
        const fld_fr_rest='fld_fr_rest';
        /**
        * Profils par défaut
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> None </li>
        * </ul>
        */ 
        const fld_fr_prof='fld_fr_prof';
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const grp_fr_ident='grp_fr_ident';
        /**
        * nom
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const grp_name='grp_name';
        /**
        * mail
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>link</i> mailto:%GRP_MAIL% </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const grp_mail='grp_mail';
        /**
        * sans adresse mail de groupe
        * <ul>
        * <li> <i>access</i> None </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>relation</i> GROUP-grp_hasmail </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const grp_hasmail='grp_hasmail';
        /**
        * Groupes
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> None </li>
        * </ul>
        */ 
        const grp_fr='grp_fr';
        /**
        * sous groupes
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const grp_idgroup='grp_idgroup';
        /**
        * groupes parents
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const grp_idpgroup='grp_idpgroup';
        /**
        * est rafraîchi
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>relation</i> GROUP-grp_isrefreshed </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const grp_isrefreshed='grp_isrefreshed';

    }
}