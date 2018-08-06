<?php

namespace SmartStructure {

    class Sentmessage extends \Anakeen\SmartStructures\Sentmessage\SentMessageHooks
    {
        const familyName = "SENTMESSAGE";
    }
}

namespace SmartStructure\Fields {

    class Sentmessage
    {
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const emsg_fr_ident='emsg_fr_ident';
        /**
        * Document référence
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>relation</i> x </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const emsg_refid='emsg_refid';
        /**
        * De
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const emsg_from='emsg_from';
        /**
        * Sujet
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const emsg_subject='emsg_subject';
        /**
        * Destinataires
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const emsg_t_recipient='emsg_t_recipient';
        /**
        * Type
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>relation</i> SENTMESSAGE-emsg_sendtype </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const emsg_sendtype='emsg_sendtype';
        /**
        * Destinataire
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const emsg_recipient='emsg_recipient';
        /**
        * Date
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> timestamp </li>
        * </ul>
        */ 
        const emsg_date='emsg_date';
        /**
        * Taille
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const emsg_size='emsg_size';
        /**
        * Corps de messages
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const emsg_fr_bodies='emsg_fr_bodies';
        /**
        * Texte
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const emsg_textbody='emsg_textbody';
        /**
        * Texte formaté
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> file </li>
        * </ul>
        */ 
        const emsg_htmlbody='emsg_htmlbody';
        /**
        * Attachements
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const emsg_t_attach='emsg_t_attach';
        /**
        * Fichier
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> file </li>
        * </ul>
        */ 
        const emsg_attach='emsg_attach';

    }
}