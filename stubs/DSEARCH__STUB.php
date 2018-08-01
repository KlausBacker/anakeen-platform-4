<?php

namespace SmartStructure {

    class Dsearch extends \Anakeen\SmartStructures\Dsearch\DSearchHooks
    {
        const familyName = "DSEARCH";
    }
}

namespace SmartStructure\Fields {

    class Dsearch
    {
        /** [array] Conditions */
        const se_t_detail='se_t_detail';
        /** [text] Attributs */
        const se_attrids='se_attrids';
        /** [text] Fonctions */
        const se_funcs='se_funcs';
        /** [text] Mot-clefs */
        const se_keys='se_keys';
        /** [enum] Opérateur */
        const se_ols='se_ols';
        /** [enum] Parenthèse gauche */
        const se_leftp='se_leftp';
        /** [enum] Parenthèse droite */
        const se_rightp='se_rightp';
        /** [array] Filtres */
        const se_t_filters='se_t_filters';
        /** [enum] Type */
        const se_typefilter='se_typefilter';
        /** [enum] Condition */
        const se_ol='se_ol';

    }
}