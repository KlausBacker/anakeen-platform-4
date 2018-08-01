<?php

namespace SmartStructure {

    class Exec extends \Anakeen\SmartStructures\Exec\ExecHooks
    {
        const familyName = "EXEC";
    }
}

namespace SmartStructure\Fields {

    class Exec
    {
        /** [text] Titre */
        const exec_title='exec_title';
        /** [docid("IUSER")] Exécutant */
        const exec_iduser='exec_iduser';
        /** [docid("BATCH")] Issue de */
        const exec_idref='exec_idref';
        /** [enum] Exécution */
        const exec_status='exec_status';
        /** [array] Paramètres */
        const exec_t_parameters='exec_t_parameters';
        /** [text] Variable */
        const exec_idvar='exec_idvar';
        /** [text] Valeur */
        const exec_valuevar='exec_valuevar';
        /** [text] Application */
        const exec_application='exec_application';
        /** [text] Action */
        const exec_action='exec_action';
        /** [text] Api */
        const exec_api='exec_api';
        /** [int] Période en jours */
        const exec_periodday='exec_periodday';
        /** [int] Période en heures */
        const exec_periodhour='exec_periodhour';
        /** [int] Période en minutes */
        const exec_periodmin='exec_periodmin';
        /** [text] Statut */
        const exec_state='exec_state';
        /** [longtext] Log */
        const exec_detaillog='exec_detaillog';

    }
}