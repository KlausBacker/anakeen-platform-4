/* Smart Fields Constants */

export class FIELDACCESSLAYER {
  /**
   * Properties
   * @smartType frame
   */
  static fal_fr_ident = "fal_fr_ident";
  /**
   * Title
   * @smartType text
   */
  static fal_title = "fal_title";
  /**
   * Description
   * @smartType longtext
   */
  static fal_desc = "fal_desc";
  /**
   * Structure
   * @smartType docid -1
   */
  static fal_famid = "fal_famid";
  /**
   * Fields
   * @smartType array
   */
  static fal_t_fields = "fal_t_fields";
  /**
   * Field id
   * @smartType text
   */
  static fal_fieldid = "fal_fieldid";
  /**
   * New Access
   * @smartType enum FAL-Access
   */
  static fal_fieldaccess = "fal_fieldaccess";
}
export class FIELDACCESSLAYERLIST {
  /**
   * Properties
   * @smartType frame
   */
  static fall_fr_ident = "fall_fr_ident";
  /**
   * Title
   * @smartType text
   */
  static ba_title = "ba_title";
  /**
   * Description
   * @smartType longtext
   */
  static ba_desc = "ba_desc";
  /**
   * Structure
   * @smartType docid -1
   */
  static fall_famid = "fall_famid";
  /**
   * Access Layers
   * @smartType array
   */
  static fall_t_layers = "fall_t_layers";
  /**
   * Access name
   * @smartType text
   */
  static fall_aclname = "fall_aclname";
  /**
   * Layer
   * @smartType docid FIELDACCESSLAYER
   */
  static fall_layer = "fall_layer";
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
