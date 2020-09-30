/* Smart Fields Constants */

export class BASE {
  /**
   * Basique
   * @smartType frame
   */
  static fr_basic = "fr_basic";
  /**
   * Titre
   * @smartType text
   */
  static ba_title = "ba_title";
}
export class FILE {
  /**
   * Description
   * @smartType frame
   */
  static fi_frdesc = "fi_frdesc";
  /**
   * titre
   * @smartType text
   */
  static fi_title = "fi_title";
  /**
   * titre
   * @smartType text
   */
  static fi_titlew = "fi_titlew";
  /**
   * principal
   * @smartType file
   */
  static fi_file = "fi_file";
}
export class IMAGE {
  /**
   * image
   * @smartType frame
   */
  static img_frfile = "img_frfile";
  /**
   * titre
   * @smartType text
   */
  static img_title = "img_title";
  /**
   * image
   * @smartType image
   */
  static img_file = "img_file";
}
export class IUSER {
  /**
   * État civil
   * @smartType frame
   */
  static us_fr_ident = "us_fr_ident";
  /**
   * Nom
   * @smartType text
   */
  static us_lname = "us_lname";
  /**
   * Prénom
   * @smartType text
   */
  static us_fname = "us_fname";
  /**
   * Mail
   * @smartType text
   */
  static us_mail = "us_mail";
  /**
   * Mail principal
   * @smartType text
   */
  static us_extmail = "us_extmail";
  /**
   * Identification
   * @smartType tab
   */
  static us_tab_sysinfo = "us_tab_sysinfo";
  /**
   * Identifier
   * @smartType frame
   */
  static us_fr_sysident = "us_fr_sysident";
  /**
   * Login
   * @smartType text
   */
  static us_login = "us_login";
  /**
   * Identifiant
   * @smartType text
   */
  static us_whatid = "us_whatid";
  /**
   * Mot de passe
   * @smartType frame
   */
  static us_fr_userchange = "us_fr_userchange";
  /**
   * Nouveau mot de passe
   * @smartType password
   */
  static us_passwd1 = "us_passwd1";
  /**
   * Confirmation mot de passe
   * @smartType password
   */
  static us_passwd2 = "us_passwd2";
  /**
   * Technical Settings
   * @smartType tab
   */
  static us_tab_system = "us_tab_system";
  /**
   * Identification intranet
   * @smartType frame
   */
  static us_fr_intranet = "us_fr_intranet";
  /**
   * Utilisateur id
   * @smartType account
   */
  static us_meid = "us_meid";
  /**
   * Rôles
   * @smartType array
   */
  static us_t_roles = "us_t_roles";
  /**
   * Rôle
   * @smartType account
   */
  static us_roles = "us_roles";
  /**
   * Groupe
   * @smartType account
   */
  static us_rolegorigin = "us_rolegorigin";
  /**
   * Origine
   * @smartType enum IUSER-us_rolesorigin
   */
  static us_rolesorigin = "us_rolesorigin";
  /**
   * groupes d&#39;appartenance
   * @smartType array
   */
  static us_groups = "us_groups";
  /**
   * groupe (titre)
   * @smartType text
   */
  static us_group = "us_group";
  /**
   * Groupe
   * @smartType account
   */
  static us_idgroup = "us_idgroup";
  /**
   * Délai d&#39;expiration en jours
   * @smartType int
   */
  static us_daydelay = "us_daydelay";
  /**
   * Date d&#39;expiration epoch
   * @smartType int
   */
  static us_expires = "us_expires";
  /**
   * Délai d&#39;expiration epoch
   * @smartType int
   */
  static us_passdelay = "us_passdelay";
  /**
   * Date d&#39;expiration
   * @smartType date
   */
  static us_expiresd = "us_expiresd";
  /**
   * Heure d&#39;expiration
   * @smartType time
   */
  static us_expirest = "us_expirest";
  /**
   * Suppléants
   * @smartType frame
   */
  static us_fr_substitute = "us_fr_substitute";
  /**
   * Suppléant
   * @smartType account
   */
  static us_substitute = "us_substitute";
  /**
   * Titulaires
   * @smartType account
   */
  static us_incumbents = "us_incumbents";
  /**
   * Date d&#39;activation
   * @smartType date
   */
  static us_substitute_startdate = "us_substitute_startdate";
  /**
   * Date de fin d&#39;activation
   * @smartType date
   */
  static us_substitute_enddate = "us_substitute_enddate";
  /**
   * Sécurité
   * @smartType frame
   */
  static us_fr_security = "us_fr_security";
  /**
   * État du compte
   * @smartType enum IUSER-us_status
   */
  static us_status = "us_status";
  /**
   * Échecs de connexion
   * @smartType int
   */
  static us_loginfailure = "us_loginfailure";
  /**
   * Date d&#39;expiration du compte
   * @smartType date
   */
  static us_accexpiredate = "us_accexpiredate";
  /**
   * Paramètre
   * @smartType frame
   */
  static us_fr_default = "us_fr_default";
  /**
   * Groupe par défaut
   * @smartType account
   */
  static us_defaultgroup = "us_defaultgroup";
}
export class MAIL {
  /**
   * Adresses
   * @smartType frame
   */
  static mail_fr = "mail_fr";
  /**
   * De
   * @smartType text
   */
  static mail_from = "mail_from";
  /**
   * Sujet
   * @smartType text
   */
  static mail_subject = "mail_subject";
  /**
   * Destinataires
   * @smartType array
   */
  static mail_dest = "mail_dest";
  /**
   *
   * @smartType enum MAIL-mail_copymode
   */
  static mail_copymode = "mail_copymode";
  /**
   * Notif.
   * @smartType enum MAIL-mail_sendformat
   */
  static mail_sendformat = "mail_sendformat";
  /**
   * Id destinataire
   * @smartType docid
   */
  static mail_recipid = "mail_recipid";
  /**
   * Destinataire
   * @smartType text
   */
  static mail_recip = "mail_recip";
  /**
   * Enregistrer une copie
   * @smartType enum MAIL-mail_savecopy
   */
  static mail_savecopy = "mail_savecopy";
  /**
   * Modèle de mail
   * @smartType docid MAILTEMPLATE
   */
  static mail_template = "mail_template";
  /**
   * Commentaire
   * @smartType frame
   */
  static mail_fr_cm = "mail_fr_cm";
  /**
   * Corps du message
   * @smartType htmltext
   */
  static mail_body = "mail_body";
  /**
   * Format
   * @smartType enum MAIL-mail_format
   */
  static mail_format = "mail_format";
  /**
   * Modèles
   * @smartType frame
   */
  static mail_fr_parameters = "mail_fr_parameters";
  /**
   * Modèle par défaut
   * @smartType docid MAILTEMPLATE
   */
  static mail_tpl_default = "mail_tpl_default";
}
export class MAILTEMPLATE {
  /**
   * Entête
   * @smartType frame
   */
  static tmail_fr = "tmail_fr";
  /**
   * Titre
   * @smartType text
   */
  static tmail_title = "tmail_title";
  /**
   * Sujet
   * @smartType text
   */
  static tmail_subject = "tmail_subject";
  /**
   * Smart Structure
   * @smartType docid -1
   */
  static tmail_family = "tmail_family";
  /**
   * Workflow Structure
   * @smartType docid -1
   */
  static tmail_workflow = "tmail_workflow";
  /**
   * Émetteur
   * @smartType array
   */
  static tmail_t_from = "tmail_t_from";
  /**
   * Type
   * @smartType enum MAILTEMPLATE-tmail_fromtype
   */
  static tmail_fromtype = "tmail_fromtype";
  /**
   * De
   * @smartType text
   */
  static tmail_from = "tmail_from";
  /**
   * Destinataires
   * @smartType array
   */
  static tmail_dest = "tmail_dest";
  /**
   * -
   * @smartType enum MAILTEMPLATE-tmail_copymode
   */
  static tmail_copymode = "tmail_copymode";
  /**
   * Type
   * @smartType enum MAILTEMPLATE-tmail_desttype
   */
  static tmail_desttype = "tmail_desttype";
  /**
   * Destinataire
   * @smartType text
   */
  static tmail_recip = "tmail_recip";
  /**
   * Contenu
   * @smartType frame
   */
  static tmail_fr_content = "tmail_fr_content";
  /**
   * Enregistrer une copie
   * @smartType enum MAILTEMPLATE-tmail_savecopy
   */
  static tmail_savecopy = "tmail_savecopy";
  /**
   * Avec liens
   * @smartType enum MAILTEMPLATE-tmail_ulink
   */
  static tmail_ulink = "tmail_ulink";
  /**
   * Corps
   * @smartType htmltext
   */
  static tmail_body = "tmail_body";
  /**
   * Attachements
   * @smartType array
   */
  static tmail_t_attach = "tmail_t_attach";
  /**
   * Attachement
   * @smartType text
   */
  static tmail_attach = "tmail_attach";
}
export class PDOC {
  /**
   * Basique
   * @smartType frame
   */
  static fr_basic = "fr_basic";
  /**
   * Titre
   * @smartType text
   */
  static ba_title = "ba_title";
  /**
   * Description
   * @smartType longtext
   */
  static ba_desc = "ba_desc";
  /**
   * Dynamique
   * @smartType frame
   */
  static dpdoc_fr_dyn = "dpdoc_fr_dyn";
  /**
   * Smart Structure utilisable pour les droits en fonction des smart fields &quot;account&quot;
   * @smartType docid -1
   */
  static dpdoc_famid = "dpdoc_famid";
  /**
   * Smart Structure (titre)
   * @smartType text
   */
  static dpdoc_fam = "dpdoc_fam";
}
export class ROLE {
  /**
   * Identification
   * @smartType frame
   */
  static role_fr_ident = "role_fr_ident";
  /**
   * Référence
   * @smartType text
   */
  static role_login = "role_login";
  /**
   * Libellé
   * @smartType text
   */
  static role_name = "role_name";
  /**
   * Identifiant système
   * @smartType int
   */
  static us_whatid = "us_whatid";
}
export class SENTMESSAGE {
  /**
   * Identification
   * @smartType frame
   */
  static emsg_fr_ident = "emsg_fr_ident";
  /**
   * Document référence
   * @smartType docid x
   */
  static emsg_refid = "emsg_refid";
  /**
   * De
   * @smartType text
   */
  static emsg_from = "emsg_from";
  /**
   * Sujet
   * @smartType text
   */
  static emsg_subject = "emsg_subject";
  /**
   * Destinataires
   * @smartType array
   */
  static emsg_t_recipient = "emsg_t_recipient";
  /**
   * Type
   * @smartType enum SENTMESSAGE-emsg_sendtype
   */
  static emsg_sendtype = "emsg_sendtype";
  /**
   * Destinataire
   * @smartType text
   */
  static emsg_recipient = "emsg_recipient";
  /**
   * Date
   * @smartType timestamp
   */
  static emsg_date = "emsg_date";
  /**
   * Taille
   * @smartType int
   */
  static emsg_size = "emsg_size";
  /**
   * Corps de messages
   * @smartType frame
   */
  static emsg_fr_bodies = "emsg_fr_bodies";
  /**
   * Texte
   * @smartType longtext
   */
  static emsg_textbody = "emsg_textbody";
  /**
   * Texte formaté
   * @smartType file
   */
  static emsg_htmlbody = "emsg_htmlbody";
  /**
   * Attachements
   * @smartType array
   */
  static emsg_t_attach = "emsg_t_attach";
  /**
   * Fichier
   * @smartType file
   */
  static emsg_attach = "emsg_attach";
  /**
   * Paramètres
   * @smartType frame
   */
  static emsg_fr_parameters = "emsg_fr_parameters";
  /**
   * Force la lecture seule
   * @smartType enum SENTMESSAGE-emsg_editcontrol
   */
  static emsg_editcontrol = "emsg_editcontrol";
}
export class TASK {
  /**
   * Identification
   * @smartType frame
   */
  static task_fr_ident = "task_fr_ident";
  /**
   * Title
   * @smartType text
   */
  static task_title = "task_title";
  /**
   * Executed by
   * @smartType account
   */
  static task_iduser = "task_iduser";
  /**
   * Status
   * @smartType enum TASK-task_status
   */
  static task_status = "task_status";
  /**
   * Description
   * @smartType longtext
   */
  static task_desc = "task_desc";
  /**
   * Route to execute
   * @smartType frame
   */
  static task_fr_route = "task_fr_route";
  /**
   * Namespace
   * @smartType text
   */
  static task_route_ns = "task_route_ns";
  /**
   * Name
   * @smartType text
   */
  static task_route_name = "task_route_name";
  /**
   * Method
   * @smartType enum TASK-task_method
   */
  static task_route_method = "task_route_method";
  /**
   * Arguments
   * @smartType array
   */
  static task_t_args = "task_t_args";
  /**
   * Nom
   * @smartType text
   */
  static task_arg_name = "task_arg_name";
  /**
   * Valeur
   * @smartType text
   */
  static task_arg_value = "task_arg_value";
  /**
   * Query fields
   * @smartType array
   */
  static task_t_queryfield = "task_t_queryfield";
  /**
   * Nom
   * @smartType text
   */
  static task_queryfield_name = "task_queryfield_name";
  /**
   * Valeur
   * @smartType text
   */
  static task_queryfield_value = "task_queryfield_value";
  /**
   * Schedule
   * @smartType frame
   */
  static task_fr_schedule = "task_fr_schedule";
  /**
   * Crontab periodicity
   * @smartType text
   */
  static task_crontab = "task_crontab";
  /**
   * Periodicity (comprehensive)
   * @smartType text
   */
  static task_humancrontab = "task_humancrontab";
  /**
   * Next execution date
   * @smartType timestamp
   */
  static task_nextdate = "task_nextdate";
  /**
   * Task results
   * @smartType frame
   */
  static task_fr_result = "task_fr_result";
  /**
   * Execution date
   * @smartType timestamp
   */
  static task_exec_date = "task_exec_date";
  /**
   * Execution duration
   * @smartType time
   */
  static task_exec_duration = "task_exec_duration";
  /**
   * Execution status
   * @smartType enum TASK-task_result-status
   */
  static task_exec_state_result = "task_exec_state_result";
  /**
   * Output
   * @smartType longtext
   */
  static task_exec_output = "task_exec_output";
}
export class TST_DOCENUM {
  /**
   * Cadre Html
   * @smartType frame
   */
  static tst_fr_info = "tst_fr_info";
  /**
   * Titre
   * @smartType text
   */
  static tst_title = "tst_title";
  /**
   * Enum
   * @smartType enum TST_DOCENUM-0123
   */
  static tst_enum1 = "tst_enum1";
  /**
   * Enum
   * @smartType enum TST_DOCENUM-ABCD
   */
  static tst_enuma = "tst_enuma";
}
export class TST_ORDER_01 {
  /**
   * Acteurs  Historique
   * @smartType tab
   */
  static tst01_t_histo = "tst01_t_histo";
  /**
   * Acteurs
   * @smartType frame
   */
  static tst01_f_acteurs = "tst01_f_acteurs";
  /**
   * Créateur
   * @smartType account
   */
  static tst01_createur = "tst01_createur";
  /**
   * Group d&#39;accès au document
   * @smartType account
   */
  static tst01_doc_access_grp = "tst01_doc_access_grp";
  /**
   * Date de création
   * @smartType date
   */
  static tst01_date_creation = "tst01_date_creation";
  /**
   * Historique
   * @smartType frame
   */
  static tst01_f_historique = "tst01_f_historique";
  /**
   * Date de reprise
   * @smartType timestamp
   */
  static tst01_date_reprise = "tst01_date_reprise";
  /**
   * Modifications
   * @smartType array
   */
  static tst01_a_historique = "tst01_a_historique";
  /**
   * Date
   * @smartType timestamp
   */
  static tst01_historique_date = "tst01_historique_date";
  /**
   * Acteur
   * @smartType account
   */
  static tst01_historique_auteur = "tst01_historique_auteur";
  /**
   * Commentaire
   * @smartType htmltext
   */
  static tst01_historique_commentaire = "tst01_historique_commentaire";
  /**
   * Documents
   * @smartType docid TST_ORDER_01
   */
  static tst01_historique_documents = "tst01_historique_documents";
}
export class DIR extends BASE {
  /**
   * Description
   * @smartType longtext
   */
  static ba_desc = "ba_desc";
  /**
   * Couleur intercalaire
   * @smartType color
   */
  static gui_color = "gui_color";
  /**
   * Restrictions
   * @smartType frame
   */
  static fld_fr_rest = "fld_fr_rest";
  /**
   * Tout ou rien
   * @smartType enum dir-fld_allbut
   */
  static fld_allbut = "fld_allbut";
  /**
   * Smart structure autorisées
   * @smartType array
   */
  static fld_tfam = "fld_tfam";
  /**
   * Smart structure (titre)
   * @smartType text
   */
  static fld_fam = "fld_fam";
  /**
   * Smart structure
   * @smartType docid -1
   */
  static fld_famids = "fld_famids";
  /**
   * Restriction sous Smart structure
   * @smartType enum dir-fld_subfam
   */
  static fld_subfam = "fld_subfam";
  /**
   * Profils par défaut
   * @smartType frame
   */
  static fld_fr_prof = "fld_fr_prof";
  /**
   * Profil par défaut de document
   * @smartType docid PDOC
   */
  static fld_pdocid = "fld_pdocid";
  /**
   * Profil par défaut de dossier
   * @smartType docid PDIR
   */
  static fld_pdirid = "fld_pdirid";
}
export class SEARCH extends BASE {
  /**
   * Auteur
   * @smartType account
   */
  static se_author = "se_author";
  /**
   * Critères
   * @smartType frame
   */
  static se_crit = "se_crit";
  /**
   * Mot-clef
   * @smartType text
   */
  static se_key = "se_key";
  /**
   * Requête sql
   * @smartType text
   */
  static se_sqlselect = "se_sqlselect";
  /**
   * Trié par
   * @smartType text
   */
  static se_orderby = "se_orderby";
  /**
   * Requête statique
   * @smartType text
   */
  static se_static = "se_static";
  /**
   * Révision
   * @smartType enum SEARCH-se_latest
   */
  static se_latest = "se_latest";
  /**
   * Mode
   * @smartType enum SEARCH-se_case
   */
  static se_case = "se_case";
  /**
   * Dans la poubelle
   * @smartType enum SEARCH-se_trash
   */
  static se_trash = "se_trash";
  /**
   * Inclure les données système
   * @smartType enum SEARCH-noyes
   */
  static se_sysfam = "se_sysfam";
  /**
   * Sans sous famille
   * @smartType enum SEARCH-se_famonly
   */
  static se_famonly = "se_famonly";
  /**
   * Document
   * @smartType enum SEARCH-se_acl
   */
  static se_acl = "se_acl";
  /**
   * Structure d&#39;appartenance
   * @smartType docid -1
   */
  static se_famid = "se_famid";
  /**
   * À partir du dossier
   * @smartType docid
   */
  static se_idfld = "se_idfld";
  /**
   * Dossier père courant
   * @smartType docid
   */
  static se_idcfld = "se_idcfld";
  /**
   * Profondeur de recherche
   * @smartType int
   */
  static se_sublevel = "se_sublevel";
}
export class TST_ORDER_02 extends TST_ORDER_01 {
  /**
   * Pièces jointes
   * @smartType tab
   */
  static tst02_t_attachments = "tst02_t_attachments";
  /**
   * Pièces jointes
   * @smartType frame
   */
  static tst02_f_pieces_jointes = "tst02_f_pieces_jointes";
  /**
   * Autres fichiers
   * @smartType array
   */
  static tst02_a_fichiers_associes = "tst02_a_fichiers_associes";
  /**
   * Fichier
   * @smartType file
   */
  static tst02_fichier_attache_file = "tst02_fichier_attache_file";
  /**
   * Titre
   * @smartType text
   */
  static tst02_fichier_attache_titre = "tst02_fichier_attache_titre";
  /**
   * Compléments
   * @smartType tab
   */
  static tst02_t_complements = "tst02_t_complements";
  /**
   * Liens réciproques
   * @smartType frame
   */
  static tst02_f_liens = "tst02_f_liens";
  /**
   * Voir aussi
   * @smartType array
   */
  static tst02_a_voir_aussi = "tst02_a_voir_aussi";
  /**
   * Relation
   * @smartType enum TST_ORDER_02-sysbf_voir_aussi_relation
   */
  static tst02_voir_aussi_relation = "tst02_voir_aussi_relation";
  /**
   * Document
   * @smartType docid TST_ORDER_02
   */
  static tst02_voir_aussi_document = "tst02_voir_aussi_document";
  /**
   * Intitulé
   * @smartType text
   */
  static tst02_libelle_document = "tst02_libelle_document";
  /**
   * Voir aussi (Version figée)
   * @smartType array
   */
  static tst02_a_voir_aussi_fige = "tst02_a_voir_aussi_fige";
  /**
   * Relation
   * @smartType enum TST_ORDER_02-sysbf_voir_aussi_relation_fige
   */
  static tst02_voir_aussi_relation_fige = "tst02_voir_aussi_relation_fige";
  /**
   * Document
   * @smartType docid TST_ORDER_02
   */
  static tst02_voir_aussi_document_fige = "tst02_voir_aussi_document_fige";
  /**
   * Intitulé
   * @smartType text
   */
  static tst02_libelle_document_fige = "tst02_libelle_document_fige";
  /**
   * Référencé par
   * @smartType array
   */
  static tst02_a_ref_par = "tst02_a_ref_par";
  /**
   * Document
   * @smartType docid TST_ORDER_02
   */
  static tst02_ref_par_document = "tst02_ref_par_document";
  /**
   * Référencé par (Version figée)
   * @smartType array
   */
  static tst02_a_ref_par_fige = "tst02_a_ref_par_fige";
  /**
   * Document
   * @smartType docid TST_ORDER_02
   */
  static tst02_ref_par_document_fige = "tst02_ref_par_document_fige";
  /**
   * Document
   * @smartType docid TST_ORDER_02
   */
  static tst02_ref_par_document_fige_initid =
    "tst02_ref_par_document_fige_initid";
  /**
   * Caractérisation
   * @smartType frame
   */
  static tst02_f_caracterisation = "tst02_f_caracterisation";
  /**
   * Référence ministérielle
   * @smartType text
   */
  static tst02_ref = "tst02_ref";
  /**
   * Accès
   * @smartType frame
   */
  static tst02_f_details = "tst02_f_details";
  /**
   * Visibilité finale
   * @smartType enum TST_ORDER_02-sysbf_visibility
   */
  static tst02_visibility = "tst02_visibility";
  /**
   * Visibilité nominative
   * @smartType account
   */
  static tst02_extra_visibility = "tst02_extra_visibility";
  /**
   * Visibilité (accounts)
   * @smartType account
   */
  static tst02_visibility_authorized_accounts =
    "tst02_visibility_authorized_accounts";
  /**
   * Rattachements
   * @smartType tab
   */
  static tst02_t_rattachements = "tst02_t_rattachements";
  /**
   * Domaine d&#39;application
   * @smartType frame
   */
  static tst02_f_domaine = "tst02_f_domaine";
  /**
   * Domaine d&#39;application
   * @smartType docid TST_ORDER_DOMAINE_APPLICATION
   */
  static tst02_domaine_app = "tst02_domaine_app";
  /**
   * Domaines d&#39;applications
   * @smartType docid TST_ORDER_DOMAINE_APPLICATION
   */
  static tst02_all_domaines_app = "tst02_all_domaines_app";
  /**
   * Domaine d&#39;application
   * @smartType docid TST_ORDER_DOMAINE_APPLICATION
   */
  static tst02_domaine_app_search = "tst02_domaine_app_search";
  /**
   * Processus
   * @smartType frame
   */
  static tst02_f_processus = "tst02_f_processus";
  /**
   * Processus
   * @smartType docid TST_ORDER_PROCESSUS
   */
  static tst02_processus = "tst02_processus";
  /**
   * Processus
   * @smartType docid TST_ORDER_PROCESSUS
   */
  static tst02_processus_parents = "tst02_processus_parents";
  /**
   * Activité
   * @smartType docid TST_ORDER_ACTIVITE
   */
  static tst02_activite = "tst02_activite";
  /**
   * Opération
   * @smartType docid TST_ORDER_OPERATION
   */
  static tst02_operation = "tst02_operation";
  /**
   * Système de management
   * @smartType frame
   */
  static tst02_f_systeme_mng = "tst02_f_systeme_mng";
  /**
   * Système de management
   * @smartType docid TST_ORDER_SYSTEME_MANAGEMENT
   */
  static tst02_systeme_mng = "tst02_systeme_mng";
  /**
   * Commentaires
   * @smartType tab
   */
  static tst02_t_commentaires = "tst02_t_commentaires";
  /**
   * Commentaires
   * @smartType frame
   */
  static tst02_f_commentaires = "tst02_f_commentaires";
  /**
   * Commentaires
   * @smartType array
   */
  static tst02_a_commentaires = "tst02_a_commentaires";
  /**
   * Utilisateur
   * @smartType account
   */
  static tst02_commentaire_utilisateur = "tst02_commentaire_utilisateur";
  /**
   * Commentaire
   * @smartType docid TST_ORDER_COMMENTAIRE
   */
  static tst02_commentaire_commentaire = "tst02_commentaire_commentaire";
  /**
   * Etat du commentaire
   * @smartType text
   */
  static tst02_commentaire_etat = "tst02_commentaire_etat";
  /**
   * Fichier attaché
   * @smartType file
   */
  static tst02_commentaire_fichier = "tst02_commentaire_fichier";
  /**
   * Notes Gestionnaires
   * @smartType frame
   */
  static tst02_f_notes_gestionnaires = "tst02_f_notes_gestionnaires";
  /**
   * Notes Gestionnaires
   * @smartType array
   */
  static tst02_a_notes_gestionnaires = "tst02_a_notes_gestionnaires";
  /**
   * Note Gestionnaire
   * @smartType docid TST_ORDER_NOTE_GESTIONNAIRE
   */
  static tst02_note_gestionnaire_note = "tst02_note_gestionnaire_note";
  /**
   * Utilisateur
   * @smartType account
   */
  static tst02_note_gestionnaire_utilisateur =
    "tst02_note_gestionnaire_utilisateur";
  /**
   * Général
   * @smartType frame
   */
  static tst02_f_general = "tst02_f_general";
  /**
   * Entité d&#39;entretien
   * @smartType docid TST_ORDER_ENTITE
   */
  static tst02_entite_entretien = "tst02_entite_entretien";
  /**
   * Référence interne
   * @smartType text
   */
  static tst02_ref_interne = "tst02_ref_interne";
  /**
   * Référence
   * @smartType text
   */
  static tst02_reference = "tst02_reference";
  /**
   * Paramètres
   * @smartType frame
   */
  static tst02_parametres = "tst02_parametres";
  /**
   * États clos du workflow associé à cette famille
   * @smartType array
   */
  static tst02_a_closed_states = "tst02_a_closed_states";
  /**
   * Clefs
   * @smartType text
   */
  static tst02_closed_states_keys = "tst02_closed_states_keys";
}
export class DSEARCH extends SEARCH {
  /**
   * Conditions
   * @smartType frame
   */
  static se_fr_detail = "se_fr_detail";
  /**
   * Condition
   * @smartType enum DSEARCH-se_ol
   */
  static se_ol = "se_ol";
  /**
   * Conditions
   * @smartType array
   */
  static se_t_detail = "se_t_detail";
  /**
   * Opérateur
   * @smartType enum DSEARCH-se_ols
   */
  static se_ols = "se_ols";
  /**
   * Parenthèse gauche
   * @smartType enum DSEARCH-se_leftp
   */
  static se_leftp = "se_leftp";
  /**
   * Parenthèse droite
   * @smartType enum DSEARCH-se_rightp
   */
  static se_rightp = "se_rightp";
  /**
   * Attributs
   * @smartType text
   */
  static se_attrids = "se_attrids";
  /**
   * Fonctions
   * @smartType text
   */
  static se_funcs = "se_funcs";
  /**
   * Mot-clefs
   * @smartType text
   */
  static se_keys = "se_keys";
  /**
   * Filtres
   * @smartType array
   */
  static se_t_filters = "se_t_filters";
  /**
   * Filtre
   * @smartType xml
   */
  static se_filter = "se_filter";
  /**
   * Type
   * @smartType enum DSEARCH-se_typefilter
   */
  static se_typefilter = "se_typefilter";
}
export class GROUP extends DIR {
  /**
   * basique
   * @smartType frame
   */
  static fr_basic = "fr_basic";
  /**
   * titre
   * @smartType text
   */
  static ba_title = "ba_title";
  /**
   * Restrictions
   * @smartType frame
   */
  static fld_fr_rest = "fld_fr_rest";
  /**
   * Profils par défaut
   * @smartType frame
   */
  static fld_fr_prof = "fld_fr_prof";
  /**
   * Identification
   * @smartType frame
   */
  static grp_fr_ident = "grp_fr_ident";
  /**
   * nom
   * @smartType text
   */
  static grp_name = "grp_name";
  /**
   * mail
   * @smartType text
   */
  static grp_mail = "grp_mail";
  /**
   * sans adresse mail de groupe
   * @smartType enum GROUP-grp_hasmail
   */
  static grp_hasmail = "grp_hasmail";
  /**
   * Groupes
   * @smartType frame
   */
  static grp_fr = "grp_fr";
  /**
   * sous groupes
   * @smartType account
   */
  static grp_idgroup = "grp_idgroup";
  /**
   * groupes parents
   * @smartType account
   */
  static grp_idpgroup = "grp_idpgroup";
  /**
   * est rafraîchi
   * @smartType enum GROUP-grp_isrefreshed
   */
  static grp_isrefreshed = "grp_isrefreshed";
}
export class MSEARCH extends SEARCH {
  /**
   * Critère
   * @smartType frame
   */
  static se_crit = "se_crit";
  /**
   * Les recherches
   * @smartType frame
   */
  static se_fr_searches = "se_fr_searches";
  /**
   * Ensemble de recherche
   * @smartType array
   */
  static seg_t_cond = "seg_t_cond";
  /**
   * Recherche
   * @smartType docid SEARCH
   */
  static seg_idcond = "seg_idcond";
}
export class SSEARCH extends SEARCH {
  /**
   * Fonction
   * @smartType frame
   */
  static se_fr_function = "se_fr_function";
  /**
   * Fichier PHP
   * @smartType text
   */
  static se_phpfile = "se_phpfile";
  /**
   * Fonction PHP
   * @smartType text
   */
  static se_phpfunc = "se_phpfunc";
  /**
   * Argument PHP
   * @smartType text
   */
  static se_phparg = "se_phparg";
}
export class TST_ORDER_03 extends TST_ORDER_02 {
  /**
   * Fichier attaché
   * @smartType frame
   */
  static tst03_f_fichier_attache = "tst03_f_fichier_attache";
  /**
   * Fichier
   * @smartType file
   */
  static tst03_fichier_attache_file = "tst03_fichier_attache_file";
  /**
   * Lien vers le fichier
   * @smartType text
   */
  static tst03_lien_fichier = "tst03_lien_fichier";
  /**
   * Indication concernant le lien
   * @smartType text
   */
  static tst03_lien_indication = "tst03_lien_indication";
  /**
   * Tableau annexes
   * @smartType array
   */
  static tst03_a_annexes = "tst03_a_annexes";
  /**
   * Fichier
   * @smartType file
   */
  static tst03_annexe_fichier = "tst03_annexe_fichier";
  /**
   * Titre
   * @smartType text
   */
  static tst03_annexe_titre = "tst03_annexe_titre";
  /**
   * Fichiers sources
   * @smartType array
   */
  static tst03_a_sources = "tst03_a_sources";
  /**
   * Fichier
   * @smartType file
   */
  static tst03_sources_fichier = "tst03_sources_fichier";
  /**
   * Titre
   * @smartType text
   */
  static tst03_sources_titre = "tst03_sources_titre";
  /**
   * Source du document externe
   * @smartType text
   */
  static tst03_docu_externe = "tst03_docu_externe";
  /**
   * Détails
   * @smartType frame
   */
  static tst03_f_details = "tst03_f_details";
  /**
   * Commentaires
   * @smartType htmltext
   */
  static tst03_commentaire = "tst03_commentaire";
  /**
   * Pôle et Métiers
   * @smartType frame
   */
  static tst03_f_pole_metiers = "tst03_f_pole_metiers";
  /**
   * Pôles
   * @smartType docid TST_ORDER_POLE
   */
  static tst03_pole = "tst03_pole";
  /**
   * Métiers
   * @smartType docid TST_ORDER_METIER
   */
  static tst03_metiers = "tst03_metiers";
  /**
   * Rédacteur
   * @smartType account
   */
  static tst03_redacteur = "tst03_redacteur";
  /**
   * Approbateur
   * @smartType account
   */
  static tst03_approbateur = "tst03_approbateur";
  /**
   * Gestionnaire référent
   * @smartType account
   */
  static tst03_gestionnaire = "tst03_gestionnaire";
  /**
   * Gestionnaires du domaine d&#39;application
   * @smartType account
   */
  static tst03_gestionnaires_da = "tst03_gestionnaires_da";
  /**
   * Date d&#39;approbation
   * @smartType date
   */
  static tst03_date_approbation = "tst03_date_approbation";
  /**
   * Éditions
   * @smartType frame
   */
  static tst03_f_anciennes_versions = "tst03_f_anciennes_versions";
  /**
   * Éditions
   * @smartType array
   */
  static tst03_a_anciennes_versions = "tst03_a_anciennes_versions";
  /**
   * Document
   * @smartType docid TST_ORDER_03
   */
  static tst03_ancienne_version_document = "tst03_ancienne_version_document";
  /**
   * Document de travail
   * @smartType docid TST_ORDER_03
   */
  static tst03_ancienne_version_document_travail =
    "tst03_ancienne_version_document_travail";
  /**
   * Date
   * @smartType date
   */
  static tst03_ancienne_version_date_version =
    "tst03_ancienne_version_date_version";
  /**
   * Edition
   * @smartType text
   */
  static tst03_ancienne_version_edition = "tst03_ancienne_version_edition";
  /**
   * Gestion
   * @smartType tab
   */
  static tst03_t_gestion = "tst03_t_gestion";
  /**
   * Gestion
   * @smartType frame
   */
  static tst03_f_gestion = "tst03_f_gestion";
  /**
   * Date limite de revue
   * @smartType date
   */
  static tst03_date_limite_revue = "tst03_date_limite_revue";
  /**
   * Enregistrements
   * @smartType tab
   */
  static tst03_t_enregistrements = "tst03_t_enregistrements";
  /**
   * Enregistrements
   * @smartType frame
   */
  static tst03_f_enregistrements = "tst03_f_enregistrements";
  /**
   * Enregistrements
   * @smartType array
   */
  static tst03_a_enregistrements = "tst03_a_enregistrements";
  /**
   * Enregistrement
   * @smartType text
   */
  static tst03_enregistrement = "tst03_enregistrement";
  /**
   * Identification
   * @smartType text
   */
  static tst03_enregistrement_identification =
    "tst03_enregistrement_identification";
  /**
   * Stockage
   * @smartType text
   */
  static tst03_enregistrement_stockage = "tst03_enregistrement_stockage";
  /**
   * Protection
   * @smartType text
   */
  static tst03_enregistrement_protection = "tst03_enregistrement_protection";
  /**
   * Accessibilité
   * @smartType text
   */
  static tst03_enregistrement_accessibilite =
    "tst03_enregistrement_accessibilite";
  /**
   * Durée conservation
   * @smartType text
   */
  static tst03_enregistrement_duree_conservation =
    "tst03_enregistrement_duree_conservation";
  /**
   * Élimination
   * @smartType text
   */
  static tst03_enregistrement_elimination = "tst03_enregistrement_elimination";
  /**
   * Titre
   * @smartType text
   */
  static tst03_titre = "tst03_titre";
  /**
   * Intitulé
   * @smartType text
   */
  static tst03_intitule = "tst03_intitule";
  /**
   * Edition
   * @smartType text
   */
  static tst03_edition = "tst03_edition";
  /**
   * Date de publication
   * @smartType date
   */
  static tst03_date_publication = "tst03_date_publication";
  /**
   * Type de document
   * @smartType docid TST_ORDER_TYPE_DOCUMENT
   */
  static tst03_type_doc = "tst03_type_doc";
  /**
   * Origine
   * @smartType enum TST_ORDER_03-sysgdoc_origine
   */
  static tst03_origine = "tst03_origine";
}
export class IGROUP extends GROUP {
  /**
   * Système
   * @smartType frame
   */
  static grp_fr_intranet = "grp_fr_intranet";
  /**
   * Identifiant
   * @smartType text
   */
  static us_login = "us_login";
  /**
   * Identifiant système
   * @smartType int
   */
  static us_whatid = "us_whatid";
  /**
   * Groupe id
   * @smartType account
   */
  static us_meid = "us_meid";
  /**
   * Rôles associés
   * @smartType docid ROLE
   */
  static grp_roles = "grp_roles";
}
export class REPORT extends DSEARCH {
  /**
   * Présentation
   * @smartType tab
   */
  static rep_tab_presentation = "rep_tab_presentation";
  /**
   * Présentation
   * @smartType frame
   */
  static rep_fr_presentation = "rep_fr_presentation";
  /**
   * Description
   * @smartType longtext
   */
  static rep_caption = "rep_caption";
  /**
   * Tri
   * @smartType text
   */
  static rep_sort = "rep_sort";
  /**
   * Id tri
   * @smartType text
   */
  static rep_idsort = "rep_idsort";
  /**
   * Ordre
   * @smartType enum REPORT-rep_ordersort
   */
  static rep_ordersort = "rep_ordersort";
  /**
   * Nombre de résultats par page
   * @smartType int
   */
  static rep_limit = "rep_limit";
  /**
   * Colonnes
   * @smartType array
   */
  static rep_tcols = "rep_tcols";
  /**
   * Label
   * @smartType text
   */
  static rep_lcols = "rep_lcols";
  /**
   * Id colonnes
   * @smartType text
   */
  static rep_idcols = "rep_idcols";
  /**
   * Option de présentation
   * @smartType text
   */
  static rep_displayoption = "rep_displayoption";
  /**
   * Couleur
   * @smartType color
   */
  static rep_colors = "rep_colors";
  /**
   * Pied de tableau
   * @smartType enum report-rep_foots
   */
  static rep_foots = "rep_foots";
  /**
   * Paramètres
   * @smartType frame
   */
  static rep_fr_param = "rep_fr_param";
  /**
   * Texte à afficher pour les valeurs protégées
   * @smartType htmltext
   */
  static rep_noaccesstext = "rep_noaccesstext";
  /**
   * Limite d&#39;affichage pour le nombre de rangées
   * @smartType int
   */
  static rep_maxdisplaylimit = "rep_maxdisplaylimit";
}
export class TST_ORDER_04 extends TST_ORDER_03 {
  /**
   * Le document
   * @smartType tab
   */
  static tst04_t_document = "tst04_t_document";
  /**
   * Annexes du document
   * @smartType frame
   */
  static tst04_f_annexes = "tst04_f_annexes";
  /**
   * Fichiers sources du document et des annexes
   * @smartType frame
   */
  static tst04_f_sources = "tst04_f_sources";
  /**
   * Déclinaisons de ce document
   * @smartType docid TST_ORDER_04
   */
  static tst04_declinaisons_document = "tst04_declinaisons_document";
  /**
   * Décliné depuis
   * @smartType docid TST_ORDER_04
   */
  static tst04_decline = "tst04_decline";
  /**
   * Documents abrogateurs
   * @smartType docid TST_ORDER_04
   */
  static tst04_documents_abrogateurs = "tst04_documents_abrogateurs";
  /**
   * Applicabilité
   * @smartType enum TST_ORDER_04-sysdocref_applicabilite
   */
  static tst04_applicabilite = "tst04_applicabilite";
  /**
   * Date d&#39;abrogation
   * @smartType date
   */
  static tst04_date_abrogation = "tst04_date_abrogation";
  /**
   * Dernier increment document de travail
   * @smartType text
   */
  static tst04_last_dt = "tst04_last_dt";
  /**
   * Justification de l&#39;abrogation
   * @smartType longtext
   */
  static tst04_justification_abrogation = "tst04_justification_abrogation";
  /**
   * Domaines d&#39;application de rattachement
   * @smartType docid TST_ORDER_DOMAINE_APPLICATION
   */
  static tst04_domaines_application_rattachement =
    "tst04_domaines_application_rattachement";
  /**
   * Dernier cycle de vérification
   * @smartType array
   */
  static tst04_a_dernieres_verifications = "tst04_a_dernieres_verifications";
  /**
   * Vérificateur
   * @smartType account
   */
  static tst04_dernier_verificateur = "tst04_dernier_verificateur";
  /**
   * Document de travail courant
   * @smartType docid TST_ORDER_05
   */
  static tst04_doc_travail_courant = "tst04_doc_travail_courant";
  /**
   * Accusés de lecture
   * @smartType frame
   */
  static tst04_f_accuses_lecture = "tst04_f_accuses_lecture";
  /**
   * Accusés de lecture
   * @smartType array
   */
  static tst04_a_accuses_lecture = "tst04_a_accuses_lecture";
  /**
   * Personne
   * @smartType account
   */
  static tst04_accuse_lecture_personne = "tst04_accuse_lecture_personne";
  /**
   * Date
   * @smartType date
   */
  static tst04_accuse_lecture_date = "tst04_accuse_lecture_date";
  /**
   * Versions antérieures
   * @smartType tab
   */
  static tst04_t_historique = "tst04_t_historique";
  /**
   * Commentaire sur l&#39;origine du fichier
   * @smartType longtext
   */
  static tst04_commentaire_fichier = "tst04_commentaire_fichier";
}
export class TST_ORDER_05 extends TST_ORDER_04 {
  /**
   * Consignes
   * @smartType tab
   */
  static tst05_t_consignes = "tst05_t_consignes";
  /**
   * Gestion
   * @smartType frame
   */
  static tst05_f_consignes = "tst05_f_consignes";
  /**
   * Info. consultant sur modif. en cours
   * @smartType longtext
   */
  static tst05_modif_en_cours = "tst05_modif_en_cours";
  /**
   * Décision
   * @smartType longtext
   */
  static tst04_derniere_decision = "tst04_derniere_decision";
  /**
   * Participants
   * @smartType frame
   */
  static tst05_f_participants = "tst05_f_participants";
  /**
   * Anciens participants
   * @smartType account
   */
  static tst05_anciens_participants = "tst05_anciens_participants";
  /**
   * Participants pressentis au recueil
   * @smartType account
   */
  static tst05_participants_recueil_default =
    "tst05_participants_recueil_default";
  /**
   * Participants pressentis à la vérification
   * @smartType account
   */
  static tst05_participants_verification_default =
    "tst05_participants_verification_default";
  /**
   * Document de référentiel courant
   * @smartType docid TST_ORDER_04
   */
  static tst05_docref_source = "tst05_docref_source";
  /**
   * Compléments gestionnaire
   * @smartType tab
   */
  static tst05_t_complement_gestionnaire = "tst05_t_complement_gestionnaire";
  /**
   * Compléments gestionnaire
   * @smartType frame
   */
  static tst05_f_complement_gestionnaire = "tst05_f_complement_gestionnaire";
  /**
   * Référence ministérielle
   * @smartType text
   */
  static tst05_ref_min = "tst05_ref_min";
  /**
   * Date limite de rédaction
   * @smartType date
   */
  static tst05_date_limite_redaction = "tst05_date_limite_redaction";
}
