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
   * Commentaire
   * @smartType frame
   */
  static mail_fr_cm = "mail_fr_cm";
  /**
   * Commentaire
   * @smartType longtext
   */
  static mail_cm = "mail_cm";
  /**
   * Format
   * @smartType enum MAIL-mail_format
   */
  static mail_format = "mail_format";
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
