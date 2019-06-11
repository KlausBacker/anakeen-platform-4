<?php

namespace SmartStructure {

    class Mail extends \Anakeen\SmartStructures\Mail\MailHooks
    {
        const familyName = "MAIL";
    }
}

namespace SmartStructure\Fields {

    class Mail
    {
        /**
        * Adresses
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const mail_fr='mail_fr';
        /**
        * De
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const mail_from='mail_from';
        /**
        * Sujet
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const mail_subject='mail_subject';
        /**
        * Destinataires
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const mail_dest='mail_dest';
        /**
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>relation</i> MAIL-mail_copymode </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const mail_copymode='mail_copymode';
        /**
        * Notif.
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>relation</i> MAIL-mail_sendformat </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const mail_sendformat='mail_sendformat';
        /**
        * Id destinataire
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const mail_recipid='mail_recipid';
        /**
        * Destinataire
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const mail_recip='mail_recip';
        /**
        * Enregistrer une copie
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> MAIL-mail_savecopy </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const mail_savecopy='mail_savecopy';
        /**
        * Commentaire
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const mail_fr_cm='mail_fr_cm';
        /**
        * Commentaire
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const mail_cm='mail_cm';
        /**
        * Format
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>relation</i> MAIL-mail_format </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const mail_format='mail_format';

    }
}