<?php
namespace SmartStructure {
	/** Modèle de mail  */
	class Mailtemplate extends \Dcp\Core\MailTemplate { const familyName="MAILTEMPLATE";}
}

namespace SmartStructure\Attributes {
	/** Modèle de mail  */
	class Mailtemplate {
		/** [frame] Entête */
		const tmail_fr='tmail_fr';
		/** [text] Titre */
		const tmail_title='tmail_title';
		/** [docid("x")] Famille */
		const tmail_family='tmail_family';
		/** [docid("x")] Famille cycle */
		const tmail_workflow='tmail_workflow';
		/** [array] Émetteur */
		const tmail_t_from='tmail_t_from';
		/** [enum] type */
		const tmail_fromtype='tmail_fromtype';
		/** [text] De */
		const tmail_from='tmail_from';
		/** [array] Destinataires */
		const tmail_dest='tmail_dest';
		/** [enum] - */
		const tmail_copymode='tmail_copymode';
		/** [enum] Type */
		const tmail_desttype='tmail_desttype';
		/** [text] Destinataire */
		const tmail_recip='tmail_recip';
		/** [text] Sujet */
		const tmail_subject='tmail_subject';
		/** [frame] Contenu */
		const tmail_fr_content='tmail_fr_content';
		/** [enum] Enregistrer une copie */
		const tmail_savecopy='tmail_savecopy';
		/** [enum] Avec liens */
		const tmail_ulink='tmail_ulink';
		/** [htmltext] Corps */
		const tmail_body='tmail_body';
		/** [array] Attachements */
		const tmail_t_attach='tmail_t_attach';
		/** [text] Attachement */
		const tmail_attach='tmail_attach';
		/** [enum] Format */
		const tmail_format='tmail_format';
	}
}
