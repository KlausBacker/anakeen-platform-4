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
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const exec_fr_ident='exec_fr_ident';
        /**
        * Exécutant
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> IUSER </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const exec_iduser='exec_iduser';
        /**
        * Issue de
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>relation</i> BATCH </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const exec_idref='exec_idref';
        /**
        * Titre
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const exec_title='exec_title';
        /**
        * Exécution
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>relation</i> EXEC-exec_status </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const exec_status='exec_status';
        /**
        * Exécution depuis
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> timestamp </li>
        * </ul>
        */ 
        const exec_statusdate='exec_statusdate';
        /**
        * Traitement
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const exec_fr_batch='exec_fr_batch';
        /**
        * Application
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const exec_application='exec_application';
        /**
        * Action
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const exec_action='exec_action';
        /**
        * Api
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const exec_api='exec_api';
        /**
        * Paramètres
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const exec_t_parameters='exec_t_parameters';
        /**
        * Variable
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const exec_idvar='exec_idvar';
        /**
        * Valeur
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const exec_valuevar='exec_valuevar';
        /**
        * Dates
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const exec_fr_date='exec_fr_date';
        /**
        * Précédente date d'exécution
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> timestamp </li>
        * </ul>
        */ 
        const exec_prevdate='exec_prevdate';
        /**
        * Prochaine date d'exécution
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> timestamp </li>
        * </ul>
        */ 
        const exec_nextdate='exec_nextdate';
        /**
        * À exécuter le
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> timestamp </li>
        * </ul>
        */ 
        const exec_handnextdate='exec_handnextdate';
        /**
        * Période en jours
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const exec_periodday='exec_periodday';
        /**
        * Période en heures
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const exec_periodhour='exec_periodhour';
        /**
        * Période en minutes
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const exec_periodmin='exec_periodmin';
        /**
        * Compte-rendu
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const exec_fr_cr='exec_fr_cr';
        /**
        * Date d'exécution
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>format</i> %A %d %B %Y %X </li>
        * <li> <i>type</i> timestamp </li>
        * </ul>
        */ 
        const exec_date='exec_date';
        /**
        * Durée d'exécution
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>format</i> %H:%M:%S </li>
        * <li> <i>type</i> time </li>
        * </ul>
        */ 
        const exec_elapsed='exec_elapsed';
        /**
        * Statut
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const exec_state='exec_state';
        /**
        * Détail
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> file </li>
        * </ul>
        */ 
        const exec_detail='exec_detail';
        /**
        * Log
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const exec_detaillog='exec_detaillog';

    }
}