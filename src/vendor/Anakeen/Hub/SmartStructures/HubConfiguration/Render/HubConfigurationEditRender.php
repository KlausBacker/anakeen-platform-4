<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration\Render;

use Anakeen\Ui\CommonRenderOptions;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Hubconfiguration as HubConfigurationFields;

class HubConfigurationEditRender extends \Anakeen\Ui\DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $break2 = "33%";
        $template = file_get_contents(__DIR__."/template/hub_docker_position.template.mustache");
        $options->enum(HubConfigurationFields::hub_docker_position)->setTemplate($template);
        $options->arrayAttribute(HubConfigurationFields::hub_roles)->setCollapse("none");
        $options->arrayAttribute(HubConfigurationFields::hub_component_parameters)->setCollapse("none");
        $options->text(HubConfigurationFields::hub_title)->setMaxLength(50);
        $options->frame(HubConfigurationFields::hub_security_frame)->setLabelPosition(CommonRenderOptions::nonePosition);
        $options->frame(HubConfigurationFields::hub_activated_frame)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2, "grow" => false],
                ["number" => 3, "minWidth" => $break2, "grow" => false]
            ]
        );
        $options->frame(HubConfigurationFields::hub_component_parameters)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2, "grow" => false],
                ["number" => 3, "minWidth" => $break2, "grow" => false]
            ]
        );
        $options->frame(HubConfigurationFields::hub_slot_parameters)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2, "grow" => false],
                ["number" => 3, "minWidth" => $break2, "grow" => false]
            ]
        );

        return $options;
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(HubConfigurationFields::hub_station_id, RenderAttributeVisibilities::StaticWriteVisibility);
        return $visibilities;
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
