<?php

namespace SmartStructure {

    class Msearch extends \Anakeen\SmartStructures\Msearch\MSearchHooks
    {
        const familyName = "MSEARCH";
    }
}

namespace SmartStructure\Fields {

    class Msearch
    {
        /** [array] Ensemble de recherche */
        const seg_t_cond='seg_t_cond';
        /** [docid("SEARCH")] Recherche */
        const seg_idcond='seg_idcond';

    }
}