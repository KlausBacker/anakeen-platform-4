<?php
namespace SmartStructure {
	/** Message envoyé  */
	class Sentmessage extends \Anakeen\SmartStructures\Sentmessage\SentMessage { const familyName="SENTMESSAGE";}
}

namespace SmartStructure\Attributes {
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
