<?php

namespace SmartStructure {

    class Timer extends \Anakeen\SmartStructures\Timer\TimerHooks
    {
        const familyName = "TIMER";
    }
}

namespace SmartStructure\Fields {

    class Timer
    {
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const tm_fr_ident='tm_fr_ident';
        /**
        * Titre
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tm_title='tm_title';
        /**
        * Date de référence
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tm_dyndate='tm_dyndate';
        /**
        * Famille
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> x </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const tm_family='tm_family';
        /**
        * Famille cycle
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> x </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const tm_workflow='tm_workflow';
        /**
        * Configuration
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const tm_t_config='tm_t_config';
        /**
        * Nombre d'itérations
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const tm_iteration='tm_iteration';
        /**
        * Modèle de mail
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> MAILTEMPLATE </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const tm_tmail='tm_tmail';
        /**
        * Nouvel état
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tm_state='tm_state';
        /**
        * Méthode
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tm_method='tm_method';

    }
}