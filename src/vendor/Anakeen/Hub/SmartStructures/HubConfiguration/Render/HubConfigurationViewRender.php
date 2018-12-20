<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration\Render;

use Dcp\Ui\RenderAttributeVisibilities;
use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Hubconfiguration as HubConfigurationFields;

class HubConfigurationViewRender extends \Anakeen\Ui\DefaultConfigViewRender
{
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility("hub_language_code", \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        return $visibilities;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);


        $break2 = "33%";
        $break3 = "50%";
        $options->arrayAttribute(HubConfigurationFields::hub_titles)->setRowMinLimit(1);
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
        $options->frame(HubConfigurationFields::hub_config)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2, "grow" => false]
            ]
        );
        return $options;
    }
}
