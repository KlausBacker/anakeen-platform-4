<?php
namespace Dcp\Family {
	/** Mail  */
	class Mail extends \Dcp\Core\MailEdit { const familyName="MAIL";}
	/** Modèle de mail  */
	class Mailtemplate extends \Dcp\Core\MailTemplate { const familyName="MAILTEMPLATE";}
	/** Message envoyé  */
	class Sentmessage extends \Dcp\Core\SentEmail { const familyName="SENTMESSAGE";}
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
	/** Message envoyé  */
	class Sentmessage {
		/** [frame] Identification */
		const emsg_fr_ident='emsg_fr_ident';
		/** [docid("x")] Document référence */
		const emsg_refid='emsg_refid';
		/** [text] De */
		const emsg_from='emsg_from';
		/** [array] Destinataires */
		const emsg_t_recipient='emsg_t_recipient';
		/** [enum] Type */
		const emsg_sendtype='emsg_sendtype';
		/** [text] Destinataire */
		const emsg_recipient='emsg_recipient';
		/** [text] Sujet */
		const emsg_subject='emsg_subject';
		/** [timestamp("%d %B %Y %H:%S")] Date */
		const emsg_date='emsg_date';
		/** [int] Taille */
		const emsg_size='emsg_size';
		/** [frame] Corps de messages */
		const emsg_fr_bodies='emsg_fr_bodies';
		/** [longtext] Texte */
		const emsg_textbody='emsg_textbody';
		/** [ifile] Texte formaté */
		const emsg_htmlbody='emsg_htmlbody';
		/** [array] Attachements */
		const emsg_t_attach='emsg_t_attach';
		/** [file] Fichier */
		const emsg_attach='emsg_attach';
		/** [frame] Paramètres */
		const emsg_fr_parameters='emsg_fr_parameters';
		/** [enum] Force la lecture seule */
		const emsg_editcontrol='emsg_editcontrol';
	}
}
