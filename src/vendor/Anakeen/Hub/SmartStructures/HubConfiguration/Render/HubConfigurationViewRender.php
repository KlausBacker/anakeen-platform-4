<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration\Render;

use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Hubconfiguration as HubConfigurationFields;

class HubConfigurationViewRender extends \Anakeen\Ui\DefaultConfigViewRender
{
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(HubConfigurationFields::hub_icon_enum, RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubConfigurationFields::hub_final_icon, RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubConfigurationFields::hub_station_id_frame, RenderAttributeVisibilities::HiddenVisibility);
        return $visibilities;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $break2 = "33%";
        $break3 = "50%";
        $options->arrayAttribute(HubConfigurationFields::hub_titles)->setRowMinLimit(1);
        $options->arrayAttribute(HubConfigurationFields::hub_titles)->setCollapse("none");
        $options->arrayAttribute(HubConfigurationFields::hub_roles)->setCollapse("none");
        $options->arrayAttribute(HubConfigurationFields::hub_component_parameters)->setCollapse("none");
        $options->frame(HubConfigurationFields::hub_component_parameters)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2, "maxWidth" => $break3],
                ["number" => 3, "minWidth" => $break2, "grow" => false]
            ]
        );
        $options->frame(HubConfigurationFields::hub_slot_parameters)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2, "maxWidth" => $break3],
                ["number" => 3, "minWidth" => $break2, "grow" => false]
            ]
        );
        $options->frame(HubConfigurationFields::hub_activated_frame)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2, "grow" => false],
                ["number" => 3, "minWidth" => $break2, "grow" => false]
            ]
        );
        return $options;
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement|null $document
     * @return array|string[]
     * @throws \Anakeen\Ui\Exception
     */
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $parent = parent::getJsReferences();
        $path = UIGetAssetPath::getElementAssets("hub", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $parent["hubConfiguration"] = $path["hubConfiguration"]["js"];
        return $parent;
    }
}
