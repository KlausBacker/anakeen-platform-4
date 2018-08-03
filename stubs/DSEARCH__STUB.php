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
        /**
        * Conditions
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const se_fr_detail='se_fr_detail';
        /**
        * Condition
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> DSEARCH-se_ol </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const se_ol='se_ol';
        /**
        * Conditions
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const se_t_detail='se_t_detail';
        /**
        * Opérateur
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> DSEARCH-se_ols </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const se_ols='se_ols';
        /**
        * Parenthèse gauche
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> DSEARCH-se_leftp </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const se_leftp='se_leftp';
        /**
        * Parenthèse droite
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> DSEARCH-se_rightp </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const se_rightp='se_rightp';
        /**
        * Attributs
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const se_attrids='se_attrids';
        /**
        * Fonctions
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const se_funcs='se_funcs';
        /**
        * Mot-clefs
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const se_keys='se_keys';
        /**
        * Filtres
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> None </li>
        * </ul>
        */ 
        const se_t_filters='se_t_filters';
        /**
        * Type
        * <ul>
        * <li> <i>access</i> None </li>
        * <li> <i>relation</i> DSEARCH-se_typefilter </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const se_typefilter='se_typefilter';

    }
}