/* Smart Fields Constants */
import * as SdeFields from "@anakeen/smart-data-engine/constants/smartStructureFields.js";
export class TIMER {
  /**
   * Identification
   * @smartType frame
   */
  static tm_fr_ident = "tm_fr_ident";
  /**
   * Titre
   * @smartType text
   */
  static tm_title = "tm_title";
  /**
   * Date de référence
   * @smartType text
   */
  static tm_dyndate = "tm_dyndate";
  /**
   * Décalage de la date de référence
   * @smartType text
   */
  static tm_deltainterval = "tm_deltainterval";
  /**
   * Famille
   * @smartType docid -1
   */
  static tm_family = "tm_family";
  /**
   * Famille cycle
   * @smartType docid -1
   */
  static tm_workflow = "tm_workflow";
  /**
   * Décalage (en jours)Obsolète
   * @smartType double
   */
  static tm_refdaydelta = "tm_refdaydelta";
  /**
   * Décalage (en heures)Obsolète
   * @smartType double
   */
  static tm_refhourdelta = "tm_refhourdelta";
  /**
   * Configuration
   * @smartType array
   */
  static tm_t_config = "tm_t_config";
  /**
   * Délai relatif (en jours) Obsolète
   * @smartType double
   */
  static tm_delay = "tm_delay";
  /**
   * Délai relatif(en heures) Obsolète
   * @smartType double
   */
  static tm_hdelay = "tm_hdelay";
  /**
   * Délai
   * @smartType text
   */
  static tm_taskinterval = "tm_taskinterval";
  /**
   * Nouvel état
   * @smartType text
   */
  static tm_state = "tm_state";
  /**
   * Méthode
   * @smartType text
   */
  static tm_method = "tm_method";
  /**
   * Nombre d&#39;itérations
   * @smartType int
   */
  static tm_iteration = "tm_iteration";
  /**
   * Modèle de mail
   * @smartType docid MAILTEMPLATE
   */
  static tm_tmail = "tm_tmail";
}
export class WDOC extends SdeFields.BASE {
  /**
   * description
   * @smartType longtext
   */
  static wf_desc = "wf_desc";
  /**
   * Structure
   * @smartType docid -1
   */
  static wf_famid = "wf_famid";
  /**
   * Structure (titre)
   * @smartType text
   */
  static wf_fam = "wf_fam";
  /**
   * Profil dynamique
   * @smartType frame
   */
  static dpdoc_fr_dyn = "dpdoc_fr_dyn";
  /**
   * Structure
   * @smartType docid -1
   */
  static dpdoc_famid = "dpdoc_famid";
  /**
   * Structure (titre)
   * @smartType text
   */
  static dpdoc_fam = "dpdoc_fam";
  /**
   * Étapes
   * @smartType tab
   */
  static wf_tab_states = "wf_tab_states";
  /**
   * Transitions
   * @smartType tab
   */
  static wf_tab_transitions = "wf_tab_transitions";
}
