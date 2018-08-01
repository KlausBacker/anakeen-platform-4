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
        /** [text] Référence */
        const role_login='role_login';
        /** [text] Libellé */
        const role_name='role_name';
        /** [int] Identifiant système */
        const us_whatid='us_whatid';

    }
}