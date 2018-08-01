<?php

namespace SmartStructure {

    class Ssearch extends \Anakeen\SmartStructures\Ssearch\SSearchHooks
    {
        const familyName = "SSEARCH";
    }
}

namespace SmartStructure\Fields {

    class Ssearch
    {
        /** [text] Fichier PHP */
        const se_phpfile='se_phpfile';
        /** [text] Fonction PHP */
        const se_phpfunc='se_phpfunc';
        /** [text] Argument PHP */
        const se_phparg='se_phparg';

    }
}