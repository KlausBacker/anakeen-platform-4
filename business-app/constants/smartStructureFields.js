/* Smart Fields Constants */
import * as HubFields from "@anakeen/hub-components/constants/smartStructureFields.js";
export class HUBBUSINESSAPP extends HubFields.HUBCONFIGURATIONVUE {
  /**
   * Business App icon
   * @smartType image
   */
  static hba_icon_image = "hba_icon_image";
  /**
   * Business App Titles
   * @smartType array
   */
  static hba_titles = "hba_titles";
  /**
   * Title
   * @smartType text
   */
  static hba_title = "hba_title";
  /**
   * Language
   * @smartType enum HBA_SUPPORT_LANG
   */
  static hba_language = "hba_language";
  /**
   * Business App collections
   * @smartType array
   */
  static hba_collections = "hba_collections";
  /**
   * Collection
   * @smartType docid DSEARCH
   */
  static hba_collection = "hba_collection";
  /**
   * Welcome Tab options
   * @smartType frame
   */
  static hba_options = "hba_options";
  /**
   * Enable
   * @smartType enum HBA_YES_NO_ENUM
   */
  static hba_welcome_option = "hba_welcome_option";
  /**
   * Title HTML template
   * @smartType longtext
   */
  static hba_welcome_title = "hba_welcome_title";
  /**
   * Smart Structure creation
   * @smartType array
   */
  static hba_structure_creation = "hba_structure_creation";
  /**
   * Structure
   * @smartType docid -1
   */
  static hba_structure = "hba_structure";
  /**
   * Grid collections
   * @smartType array
   */
  static hba_grid_collections = "hba_grid_collections";
  /**
   * Grid collection
   * @smartType docid REPORT
   */
  static hba_grid_collection = "hba_grid_collection";
}
