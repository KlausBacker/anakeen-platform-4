<?php

namespace SmartStructure {

    class Pdoc extends \Anakeen\SmartStructures\Profiles\PDocHooks
    {
        const familyName = "PDOC";
    }
}

namespace SmartStructure\Fields {

    class Pdoc
    {
        /** [text] Titre */
        const ba_title='ba_title';
        /** [longtext] Description */
        const ba_desc='ba_desc';
        /** [text] Smart Structure (titre) */
        const dpdoc_fam='dpdoc_fam';
        /** [docid("-1")] Smart Structure utilisable pour les droits en fonction des attributs "account" */
        const dpdoc_famid='dpdoc_famid';

    }
}