<?php

namespace SmartStructure {

    class Search extends \Anakeen\SmartStructures\Search\SearchHooks
    {
        const familyName = "SEARCH";
    }
}

namespace SmartStructure\Fields {

    class Search
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * </ul>
        */ 
        const fr_basic='fr_basic';
        /**
        * Auteur
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const se_author='se_author';
        /**
        * Critère
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const se_crit='se_crit';
        /**
        * Mot-clef
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const se_key='se_key';
        /**
        * Requête sql
        * <ul>
        * <li> <i>access</i> None </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const se_sqlselect='se_sqlselect';
        /**
        * Trié par
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const se_orderby='se_orderby';
        /**
        * Requête statique
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const se_static='se_static';
        /**
        * Révision
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> SEARCH-se_latest </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const se_latest='se_latest';
        /**
        * Mode
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> SEARCH-se_case </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const se_case='se_case';
        /**
        * Dans la poubelle
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> SEARCH-se_trash </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const se_trash='se_trash';
        /**
        * Inclure les données système
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> SEARCH-noyes </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const se_sysfam='se_sysfam';
        /**
        * Sans sous famille
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> SEARCH-se_famonly </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const se_famonly='se_famonly';
        /**
        * Document
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> SEARCH-se_acl </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const se_acl='se_acl';
        /**
        * Structure d'appartenance
        * <ul>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const se_famid='se_famid';
        /**
        * À partir du dossier
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const se_idfld='se_idfld';
        /**
        * Dossier père courant
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const se_idcfld='se_idcfld';
        /**
        * Profondeur de recherche
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const se_sublevel='se_sublevel';

    }
}