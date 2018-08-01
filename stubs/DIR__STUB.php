<?php

namespace SmartStructure {

    class Dir extends \Anakeen\SmartStructures\Dir\DirHooks
    {
        const familyName = "DIR";
    }
}

namespace SmartStructure\Fields {

    class Dir
    {
        /** [longtext] Description */
        const ba_desc='ba_desc';
        /** [array] Smart structure autorisées */
        const fld_tfam='fld_tfam';
        /** [text] Smart structure (titre) */
        const fld_fam='fld_fam';
        /** [docid("-1")] Smart structure */
        const fld_famids='fld_famids';
        /** [enum] Restriction sous Smart structure */
        const fld_subfam='fld_subfam';
        /** [enum] Tout ou rien */
        const fld_allbut='fld_allbut';
        /** [docid("PDOC")] Profil par défaut de document */
        const fld_pdocid='fld_pdocid';
        /** [docid("PDIR")] Profil par défaut de dossier */
        const fld_pdirid='fld_pdirid';

    }
}