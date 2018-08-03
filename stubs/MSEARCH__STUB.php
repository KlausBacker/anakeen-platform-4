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
        /**
        * Critère
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> None </li>
        * </ul>
        */ 
        const se_crit='se_crit';
        /**
        * Les recherches
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const se_fr_searches='se_fr_searches';
        /**
        * Ensemble de recherche
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const seg_t_cond='seg_t_cond';
        /**
        * Recherche
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> SEARCH </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const seg_idcond='seg_idcond';

    }
}