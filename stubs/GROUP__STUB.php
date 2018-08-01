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
        /** [text] titre */
        const ba_title='ba_title';
        /** [text] nom */
        const grp_name='grp_name';
        /** [text] mail */
        const grp_mail='grp_mail';
        /** [enum] sans adresse mail de groupe */
        const grp_hasmail='grp_hasmail';
        /** [enum] est rafraîchi */
        const grp_isrefreshed='grp_isrefreshed';

    }
}