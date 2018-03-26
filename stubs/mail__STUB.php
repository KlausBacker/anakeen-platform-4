<?php
namespace Dcp\Family {
	/** Mail  */
	class Mail extends \Dcp\Core\MailEdit { const familyName="MAIL";}
}

namespace Dcp\AttributeIdentifiers {
	/** Mail  */
	class Mail {
		/** [frame] Adresses */
		const mail_fr='mail_fr';
		/** [text] De */
		const mail_from='mail_from';
		/** [array] Destinataires */
		const mail_dest='mail_dest';
		/** [enum]  */
		const mail_copymode='mail_copymode';
		/** [docid] Id destinataire */
		const mail_recipid='mail_recipid';
		/** [text] Destinataire */
		const mail_recip='mail_recip';
		/** [enum] Notif. */
		const mail_sendformat='mail_sendformat';
		/** [text] Sujet */
		const mail_subject='mail_subject';
		/** [enum] Enregistrer une copie */
		const mail_savecopy='mail_savecopy';
		/** [frame] Commentaire */
		const mail_fr_cm='mail_fr_cm';
		/** [longtext] Commentaire */
		const mail_cm='mail_cm';
		/** [enum] Format */
		const mail_format='mail_format';
	}
}
