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
        /** [text] identifiant */
        const us_login='us_login';
        /** [docid("ROLE")] Rôles associés */
        const grp_roles='grp_roles';
        /** [int] identifiant système */
        const us_whatid='us_whatid';

    }
}