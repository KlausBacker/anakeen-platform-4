/* Smart Fields Constants */

export class ADMINPARAMETERSHUBCONFIGURATION extends HUBCONFIGURATIONGENERIC {
  /**
   * Options
   * @smartType frame
   */
  static admin_hub_configuration_options = "admin_hub_configuration_options";
  /**
   * Paramètres généraux
   * @smartType enum Hub_YesNo
   */
  static admin_hub_configuration_global = "admin_hub_configuration_global";
  /**
   * Paramètres utilisateurs
   * @smartType enum Hub_YesNo
   */
  static admin_hub_configuration_user = "admin_hub_configuration_user";
  /**
   * Utilisateur spécifique
   * @smartType account
   */
  static admin_hub_configuration_account = "admin_hub_configuration_account";
  /**
   * Namespace
   * @smartType text
   */
  static admin_hub_configuration_namespace =
    "admin_hub_configuration_namespace";
}
