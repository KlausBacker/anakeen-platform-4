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
        /** [array] Destinataires */
        const mail_dest='mail_dest';
        /** [text] Destinataire */
        const mail_recip='mail_recip';
        /** [docid("undefined")] Id destinataire */
        const mail_recipid='mail_recipid';
        /** [enum] undefined */
        const mail_copymode='mail_copymode';
        /** [enum] Notif. */
        const mail_sendformat='mail_sendformat';
        /** [text] De */
        const mail_from='mail_from';
        /** [text] Sujet */
        const mail_subject='mail_subject';
        /** [enum] Enregistrer une copie */
        const mail_savecopy='mail_savecopy';
        /** [longtext] Commentaire */
        const mail_cm='mail_cm';
        /** [enum] Format */
        const mail_format='mail_format';

    }
}