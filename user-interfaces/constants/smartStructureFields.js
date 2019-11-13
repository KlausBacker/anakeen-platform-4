/* Smart Fields Constants */
import * as SdeFields from "@anakeen/smart-data-engine/constants/smartStructureFields.js";
export class CVDOC extends SdeFields.BASE {
  /**
   * Description
   * @smartType longtext
   */
  static ba_desc = "ba_desc";
  /**
   * Structure
   * @smartType docid -1
   */
  static cv_famid = "cv_famid";
  /**
   * Vues
   * @smartType array
   */
  static cv_t_views = "cv_t_views";
  /**
   * Identifiant de la vue
   * @smartType text
   */
  static cv_idview = "cv_idview";
  /**
   * Label
   * @smartType text
   */
  static cv_lview = "cv_lview";
  /**
   * Classe de configuration de rendu (HTML5)
   * @smartType text
   */
  static cv_renderconfigclass = "cv_renderconfigclass";
  /**
   * Menu
   * @smartType text
   */
  static cv_menu = "cv_menu";
  /**
   * Type
   * @smartType enum CVDOC-cv_kview
   */
  static cv_kview = "cv_kview";
  /**
   * Affichable
   * @smartType enum CVDOC-cv_displayed
   */
  static cv_displayed = "cv_displayed";
  /**
   * Masque
   * @smartType docid MASK
   */
  static cv_mskid = "cv_mskid";
  /**
   * Ordre de sélection
   * @smartType int
   */
  static cv_order = "cv_order";
  /**
   * Vues par défauts
   * @smartType frame
   */
  static cv_fr_default = "cv_fr_default";
  /**
   * Id création vues par défaut
   * @smartType text
   */
  static cv_idcview = "cv_idcview";
  /**
   * Création vue
   * @smartType text
   */
  static cv_lcview = "cv_lcview";
  /**
   * Classe d&#39;accès au rendu
   * @smartType text
   */
  static cv_renderaccessclass = "cv_renderaccessclass";
  /**
   * Masque primaire
   * @smartType docid MASK
   */
  static cv_primarymask = "cv_primarymask";
  /**
   * Profil dynamique
   * @smartType frame
   */
  static dpdoc_fr_dyn = "dpdoc_fr_dyn";
  /**
   * Structure pour le profil
   * @smartType docid -1
   */
  static dpdoc_famid = "dpdoc_famid";
}
export class MASK extends SdeFields.BASE {
  /**
   * Information
   * @smartType frame
   */
  static msk_fr_rest = "msk_fr_rest";
  /**
   * Structure
   * @smartType docid -1
   */
  static msk_famid = "msk_famid";
  /**
   * Fields
   * @smartType array
   */
  static msk_t_contain = "msk_t_contain";
  /**
   * Field
   * @smartType text
   */
  static msk_attrids = "msk_attrids";
  /**
   * Visibilité
   * @smartType text
   */
  static msk_visibilities = "msk_visibilities";
  /**
   * Obligatoire
   * @smartType enum MASK-needed
   */
  static msk_needeeds = "msk_needeeds";
}
