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
        /** [text] nom */
        const us_lname='us_lname';
        /** [text] prénom */
        const us_fname='us_fname';
        /** [text] mail */
        const us_mail='us_mail';
        /** [text] mail principal */
        const us_extmail='us_extmail';
        /** [text] login */
        const us_login='us_login';
        /** [text] identifiant */
        const us_whatid='us_whatid';
        /** [array] Rôles */
        const us_t_roles='us_t_roles';
        /** [enum] Origine */
        const us_rolesorigin='us_rolesorigin';
        /** [array] groupes d'appartenance */
        const us_groups='us_groups';
        /** [text] groupe (titre) */
        const us_group='us_group';
        /** [int] délai d'expiration en jours */
        const us_daydelay='us_daydelay';
        /** [int] date d'expiration epoch */
        const us_expires='us_expires';
        /** [int] délai d'expiration epoch */
        const us_passdelay='us_passdelay';
        /** [enum] état du compte */
        const us_status='us_status';
        /** [int] échecs de connexion */
        const us_loginfailure='us_loginfailure';

    }
}