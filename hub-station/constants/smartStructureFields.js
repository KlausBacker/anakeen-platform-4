/* Smart Fields Constants */

export class HUBCONFIGURATION {
  /**
   * Configuration
   * @smartType tab
   */
  static hub_config = "hub_config";
  /**
   * Identification
   * @smartType frame
   */
  static hub_station_id_frame = "hub_station_id_frame";
  /**
   * Hub Station
   * @smartType docid HUBINSTANCIATION
   */
  static hub_station_id = "hub_station_id";
  /**
   * Name
   * @smartType text
   */
  static hub_title = "hub_title";
  /**
   * Dock
   * @smartType frame
   */
  static hub_slot_parameters = "hub_slot_parameters";
  /**
   * Order in dock
   * @smartType int
   */
  static hub_order = "hub_order";
  /**
   * Dock position
   * @smartType enum Hub_DockerPos
   */
  static hub_docker_position = "hub_docker_position";
  /**
   * Hub element status
   * @smartType frame
   */
  static hub_activated_frame = "hub_activated_frame";
  /**
   * Element is default selected
   * @smartType enum Hub_YesNo
   */
  static hub_activated = "hub_activated";
  /**
   * Element is selectable
   * @smartType enum Hub_YesNo
   */
  static hub_selectable = "hub_selectable";
  /**
   * Element is expandable
   * @smartType enum Hub_YesNo
   */
  static hub_expandable = "hub_expandable";
  /**
   * Priority
   * @smartType int
   */
  static hub_activated_order = "hub_activated_order";
  /**
   * Element parameters
   * @smartType tab
   */
  static hub_component_tab = "hub_component_tab";
  /**
   * Parameters
   * @smartType frame
   */
  static hub_component_parameters = "hub_component_parameters";
  /**
   * Security
   * @smartType tab
   */
  static hub_security = "hub_security";
  /**
   * Security Roles
   * @smartType frame
   */
  static hub_security_frame = "hub_security_frame";
  /**
   * Roles to display hub element
   * @smartType account
   */
  static hub_visibility_roles = "hub_visibility_roles";
  /**
   * Roles to access hub element API
   * @smartType account
   */
  static hub_execution_roles = "hub_execution_roles";
  /**
   * Security access
   * @smartType frame
   */
  static hub_p_securityaccess = "hub_p_securityaccess";
  /**
   * Mandatory route role
   * @smartType text
   */
  static hub_p_routes_role = "hub_p_routes_role";
}
export class HUBINSTANCIATION {
  /**
   * Configuration
   * @smartType tab
   */
  static hub_instance_config = "hub_instance_config";
  /**
   * Information
   * @smartType frame
   */
  static hub_instance = "hub_instance";
  /**
   * Logical Name
   * @smartType text
   */
  static instance_logical_name = "instance_logical_name";
  /**
   * Router entry
   * @smartType text
   */
  static hub_instanciation_router_entry = "hub_instanciation_router_entry";
  /**
   * Fav icon
   * @smartType image
   */
  static hub_instanciation_icone = "hub_instanciation_icone";
  /**
   * Label
   * @smartType frame
   */
  static hub_instance_label_frame = "hub_instance_label_frame";
  /**
   * Hub Station Titles
   * @smartType array
   */
  static hub_instance_titles = "hub_instance_titles";
  /**
   * Title
   * @smartType text
   */
  static hub_instance_title = "hub_instance_title";
  /**
   * Language
   * @smartType text
   */
  static hub_instance_language = "hub_instance_language";
  /**
   * Advanced Settings
   * @smartType frame
   */
  static hub_instance_advanced_settings = "hub_instance_advanced_settings";
  /**
   * Global assets
   * @smartType tab
   */
  static hub_instance_tab_assets = "hub_instance_tab_assets";
  /**
   * Hub instance global assets
   * @smartType frame
   */
  static hub_instance_fr_assets = "hub_instance_fr_assets";
  /**
   * Javascript assets
   * @smartType array
   */
  static hub_instance_jsassets = "hub_instance_jsassets";
  /**
   * Asset type
   * @smartType enum hub_instance_asset_type
   */
  static hub_instance_jsasset_type = "hub_instance_jsasset_type";
  /**
   * Location
   * @smartType text
   */
  static hub_instance_jsasset = "hub_instance_jsasset";
  /**
   * CSS assets
   * @smartType array
   */
  static hub_instance_cssassets = "hub_instance_cssassets";
  /**
   * Asset type
   * @smartType enum hub_instance_asset_type
   */
  static hub_instance_cssasset_type = "hub_instance_cssasset_type";
  /**
   * Location
   * @smartType text
   */
  static hub_instance_cssasset = "hub_instance_cssasset";
  /**
   * Security
   * @smartType tab
   */
  static hub_security = "hub_security";
  /**
   *
   * @smartType frame
   */
  static hub_security_frame = "hub_security_frame";
  /**
   * Roles to access
   * @smartType account
   */
  static hub_access_roles = "hub_access_roles";
  /**
   * Role to access to any elements
   * @smartType account
   */
  static hub_super_role = "hub_super_role";
}
export class HUBCONFIGURATIONSLOT extends HUBCONFIGURATION {}
export class HUBCONFIGURATIONVUE extends HUBCONFIGURATION {
  /**
   * Router entry
   * @smartType text
   */
  static hub_vue_router_entry = "hub_vue_router_entry";
}
export class HUBCONFIGURATIONGENERIC extends HUBCONFIGURATIONVUE {
  /**
   * Assets
   * @smartType frame
   */
  static hge_fr_assets = "hge_fr_assets";
  /**
   * Javascript assets
   * @smartType array
   */
  static hge_jsassets = "hge_jsassets";
  /**
   * Asset type
   * @smartType enum hge_asset_type
   */
  static hge_jsasset_type = "hge_jsasset_type";
  /**
   * Location
   * @smartType text
   */
  static hge_jsasset = "hge_jsasset";
  /**
   * CSS assets
   * @smartType array
   */
  static hge_cssassets = "hge_cssassets";
  /**
   * Asset type
   * @smartType enum hge_asset_type
   */
  static hge_cssasset_type = "hge_cssasset_type";
  /**
   * Location
   * @smartType text
   */
  static hge_cssasset = "hge_cssasset";
  /**
   * Identification
   * @smartType frame
   */
  static hge_fr_identification = "hge_fr_identification";
  /**
   * Component tag
   * @smartType text
   */
  static hge_component_tag = "hge_component_tag";
}
export class HUBCONFIGURATIONIDENTITY extends HUBCONFIGURATIONSLOT {
  /**
   * Email alterable
   * @smartType enum Hub_YesNo
   */
  static email_alterable = "email_alterable";
  /**
   * Password alterable
   * @smartType enum Hub_YesNo
   */
  static password_alterable = "password_alterable";
}
export class HUBCONFIGURATIONLABEL extends HUBCONFIGURATIONSLOT {
  /**
   * Html label
   * @smartType longtext
   */
  static label = "label";
  /**
   * Extended Html label
   * @smartType longtext
   */
  static extended_label = "extended_label";
}
export class HUBCONFIGURATIONLOGOUT extends HUBCONFIGURATIONSLOT {
  /**
   * Title
   * @smartType text
   */
  static logout_title = "logout_title";
}
