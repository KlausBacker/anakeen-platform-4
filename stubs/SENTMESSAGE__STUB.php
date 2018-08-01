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
        /** [array] Destinataires */
        const emsg_t_recipient='emsg_t_recipient';
        /** [text] Destinataire */
        const emsg_recipient='emsg_recipient';
        /** [enum] Type */
        const emsg_sendtype='emsg_sendtype';
        /** [text] De */
        const emsg_from='emsg_from';
        /** [text] Sujet */
        const emsg_subject='emsg_subject';
        /** [docid("x")] Document référence */
        const emsg_refid='emsg_refid';
        /** [int] Taille */
        const emsg_size='emsg_size';
        /** [array] Attachements */
        const emsg_t_attach='emsg_t_attach';
        /** [longtext] Texte */
        const emsg_textbody='emsg_textbody';

    }
}