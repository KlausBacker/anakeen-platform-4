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
        /** [array] Émetteur */
        const tmail_t_from='tmail_t_from';
        /** [text] De */
        const tmail_from='tmail_from';
        /** [enum] Type */
        const tmail_fromtype='tmail_fromtype';
        /** [array] Destinataires */
        const tmail_dest='tmail_dest';
        /** [text] Destinataire */
        const tmail_recip='tmail_recip';
        /** [enum] - */
        const tmail_copymode='tmail_copymode';
        /** [enum] Type */
        const tmail_desttype='tmail_desttype';
        /** [text] Titre */
        const tmail_title='tmail_title';
        /** [text] Sujet */
        const tmail_subject='tmail_subject';
        /** [docid("-1")] Smart Structure */
        const tmail_family='tmail_family';
        /** [docid("-1")] Workflow Structure */
        const tmail_workflow='tmail_workflow';
        /** [array] Attachements */
        const tmail_t_attach='tmail_t_attach';
        /** [text] Attachement */
        const tmail_attach='tmail_attach';
        /** [enum] Enregistrer une copie */
        const tmail_savecopy='tmail_savecopy';
        /** [enum] Avec liens */
        const tmail_ulink='tmail_ulink';

    }
}