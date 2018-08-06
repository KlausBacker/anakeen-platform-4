<?php

namespace SmartStructure {

    class Mailtemplate extends \Anakeen\SmartStructures\Mailtemplate\MailTemplateHooks
    {
        const familyName = "MAILTEMPLATE";
    }
}

namespace SmartStructure\Fields {

    class Mailtemplate
    {
        /**
        * Entête
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const tmail_fr='tmail_fr';
        /**
        * Titre
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tmail_title='tmail_title';
        /**
        * Sujet
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tmail_subject='tmail_subject';
        /**
        * Smart Structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const tmail_family='tmail_family';
        /**
        * Workflow Structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const tmail_workflow='tmail_workflow';
        /**
        * Émetteur
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const tmail_t_from='tmail_t_from';
        /**
        * Type
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> MAILTEMPLATE-tmail_fromtype </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const tmail_fromtype='tmail_fromtype';
        /**
        * De
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tmail_from='tmail_from';
        /**
        * Destinataires
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const tmail_dest='tmail_dest';
        /**
        * -
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> MAILTEMPLATE-tmail_copymode </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const tmail_copymode='tmail_copymode';
        /**
        * Type
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> MAILTEMPLATE-tmail_desttype </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const tmail_desttype='tmail_desttype';
        /**
        * Destinataire
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tmail_recip='tmail_recip';
        /**
        * Contenu
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const tmail_fr_content='tmail_fr_content';
        /**
        * Enregistrer une copie
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> MAILTEMPLATE-tmail_savecopy </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const tmail_savecopy='tmail_savecopy';
        /**
        * Avec liens
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> MAILTEMPLATE-tmail_ulink </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const tmail_ulink='tmail_ulink';
        /**
        * Corps
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> htmltext </li>
        * </ul>
        */ 
        const tmail_body='tmail_body';
        /**
        * Attachements
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const tmail_t_attach='tmail_t_attach';
        /**
        * Attachement
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tmail_attach='tmail_attach';

    }
}