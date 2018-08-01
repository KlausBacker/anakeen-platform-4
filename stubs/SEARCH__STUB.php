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
        /** [text] Mot-clef */
        const se_key='se_key';
        /** [text] Requête sql */
        const se_sqlselect='se_sqlselect';
        /** [text] Trié par */
        const se_orderby='se_orderby';
        /** [text] Requête statique */
        const se_static='se_static';
        /** [docid("-1")] Structure d'appartenance */
        const se_famid='se_famid';
        /** [docid("undefined")] À partir du dossier */
        const se_idfld='se_idfld';
        /** [docid("undefined")] Dossier père courant */
        const se_idcfld='se_idcfld';
        /** [enum] Révision */
        const se_latest='se_latest';
        /** [enum] Mode */
        const se_case='se_case';
        /** [enum] Dans la poubelle */
        const se_trash='se_trash';
        /** [enum] Inclure les données système */
        const se_sysfam='se_sysfam';
        /** [enum] Sans sous famille */
        const se_famonly='se_famonly';
        /** [enum] Document */
        const se_acl='se_acl';
        /** [int] Profondeur de recherche */
        const se_sublevel='se_sublevel';

    }
}