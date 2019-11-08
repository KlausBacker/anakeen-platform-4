<?php

namespace SmartStructure {

    class Hubconfigurationgeneric extends \Anakeen\Hub\SmartStructures\HubConfigurationGeneric\HubConfigurationGenericBehavior
    {
        const familyName = "HUBCONFIGURATIONGENERIC";
    }
}

namespace SmartStructure\Fields {

    class Hubconfigurationgeneric extends Hubconfigurationvue
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * </ul>
        */ 
        const hub_component_tab='hub_component_tab';
        /**
        * Assets
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hge_fr_assets='hge_fr_assets';
        /**
        * Javascript assets
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hge_jsassets='hge_jsassets';
        /**
        * Asset type
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> hge_asset_type </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hge_jsasset_type='hge_jsasset_type';
        /**
        * Location
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hge_jsasset='hge_jsasset';
        /**
        * CSS assets
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hge_cssassets='hge_cssassets';
        /**
        * Asset type
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> hge_asset_type </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const hge_cssasset_type='hge_cssasset_type';
        /**
        * Location
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hge_cssasset='hge_cssasset';
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const hge_fr_identification='hge_fr_identification';
        /**
        * Component tag
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const hge_component_tag='hge_component_tag';

    }
}