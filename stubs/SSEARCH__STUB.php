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
        /**
        * Fonction
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const se_fr_function='se_fr_function';
        /**
        * Fichier PHP
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const se_phpfile='se_phpfile';
        /**
        * Fonction PHP
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const se_phpfunc='se_phpfunc';
        /**
        * Argument PHP
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const se_phparg='se_phparg';

    }
}