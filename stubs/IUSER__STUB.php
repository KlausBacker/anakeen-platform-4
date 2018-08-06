<?php

namespace SmartStructure {

    class Iuser extends \Anakeen\SmartStructures\Iuser\IUserHooks
    {
        const familyName = "IUSER";
    }
}

namespace SmartStructure\Fields {

    class Iuser
    {
        /**
        * État civil
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const us_fr_ident='us_fr_ident';
        /**
        * nom
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const us_lname='us_lname';
        /**
        * prénom
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const us_fname='us_fname';
        /**
        * mail
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>link</i> mailto:%US_MAIL% </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const us_mail='us_mail';
        /**
        * mail principal
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const us_extmail='us_extmail';
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> tab </li>
        * <li> <i>access</i> None </li>
        * </ul>
        */ 
        const us_tab_sysinfo='us_tab_sysinfo';
        /**
        * Identifier
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const us_fr_sysident='us_fr_sysident';
        /**
        * login
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const us_login='us_login';
        /**
        * identifiant
        * <ul>
        * <li> <i>access</i> None </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const us_whatid='us_whatid';
        /**
        * Mot de passe
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> None </li>
        * </ul>
        */ 
        const us_fr_userchange='us_fr_userchange';
        /**
        * Technical Settings
        * <ul>
        * <li> <i>type</i> tab </li>
        * <li> <i>access</i> None </li>
        * </ul>
        */ 
        const us_tab_system='us_tab_system';
        /**
        * Identification intranet
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const us_fr_intranet='us_fr_intranet';
        /**
        * utilisateur id
        * <ul>
        * <li> <i>access</i> None </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const us_meid='us_meid';
        /**
        * Rôles
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const us_t_roles='us_t_roles';
        /**
        * Rôle
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const us_roles='us_roles';
        /**
        * Groupe
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const us_rolegorigin='us_rolegorigin';
        /**
        * Origine
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> IUSER-us_rolesorigin </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const us_rolesorigin='us_rolesorigin';
        /**
        * groupes d'appartenance
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const us_groups='us_groups';
        /**
        * groupe (titre)
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const us_group='us_group';
        /**
        * Groupe
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const us_idgroup='us_idgroup';
        /**
        * délai d'expiration en jours
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const us_daydelay='us_daydelay';
        /**
        * date d'expiration epoch
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const us_expires='us_expires';
        /**
        * délai d'expiration epoch
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const us_passdelay='us_passdelay';
        /**
        * date d'expiration
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> date </li>
        * </ul>
        */ 
        const us_expiresd='us_expiresd';
        /**
        * heure d'expiration
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> time </li>
        * </ul>
        */ 
        const us_expirest='us_expirest';
        /**
        * Suppléants
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const us_fr_substitute='us_fr_substitute';
        /**
        * Suppléant
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const us_substitute='us_substitute';
        /**
        * Titulaires
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const us_incumbents='us_incumbents';
        /**
        * Sécurité
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const us_fr_security='us_fr_security';
        /**
        * état du compte
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> IUSER-us_status </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const us_status='us_status';
        /**
        * échecs de connexion
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const us_loginfailure='us_loginfailure';
        /**
        * Date d'expiration du compte
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> date </li>
        * </ul>
        */ 
        const us_accexpiredate='us_accexpiredate';

    }
}