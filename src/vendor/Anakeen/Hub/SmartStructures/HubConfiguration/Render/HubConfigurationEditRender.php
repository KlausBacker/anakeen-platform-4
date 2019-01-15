<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration\Render;

use Dcp\Ui\CommonRenderOptions;
use Dcp\Ui\RenderAttributeVisibilities;
use Dcp\Ui\RenderOptions;
use Dcp\Ui\UIGetAssetPath;
use SmartStructure\Fields\Hubconfiguration as HubConfigurationFields;

class HubConfigurationEditRender extends \Anakeen\Ui\DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $break2 = "33%";
        $options->arrayAttribute(HubConfigurationFields::hub_titles)->setRowMinLimit(1);
        $options->arrayAttribute(HubConfigurationFields::hub_titles)->setCollapse("none");
        $options->arrayAttribute(HubConfigurationFields::hub_roles)->setCollapse("none");
        $options->arrayAttribute(HubConfigurationFields::hub_component_parameters)->setCollapse("none");
        $options->text(HubConfigurationFields::hub_title)->setMaxLength(50);
        $options->text(HubConfigurationFields::hub_language)->setMaxLength(15);
        $options->frame(HubConfigurationFields::hub_security_frame)->setLabelPosition(CommonRenderOptions::nonePosition);
        $options->htmltext(HubConfigurationFields::hub_icon_text)->setToolbar("Full");
        $options->image(HubConfigurationFields::hub_icon_image)->setThumbnailSize(14);
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
        $tpl = <<<HTML
<select class="icon-picker">
HTML;
        $options->enum(HubConfigurationFields::hub_icon_font)->setTemplate($tpl);
        return $options;
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(HubConfigurationFields::hub_language_code, RenderAttributeVisibilities::HiddenVisibility);
        return $visibilities;
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement|null $document
     * @return array|string[]
     * @throws \Dcp\Ui\Exception
     */
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $parent = parent::getJsReferences();
        $path = UIGetAssetPath::getElementAssets("hub", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $parent["hubConfiguration"] = $path["hubConfiguration"]["js"];
        return $parent;
    }
}
