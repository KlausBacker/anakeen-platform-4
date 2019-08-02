<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration\Render;

use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Hubconfiguration as HubConfigurationFields;

class HubConfigurationViewRender extends \Anakeen\Ui\DefaultConfigViewRender
{
    use THubConfigurationCommonRender;
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = $this->getCommonVisibilities(parent::getVisibilities($document, $mask), $document, $mask);
        $visibilities->setVisibility(HubConfigurationFields::hub_title, RenderAttributeVisibilities::HiddenVisibility);
        return $visibilities;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute(HubConfigurationFields::hub_component_parameters)->setCollapse("none");


        $options->frame(HubConfigurationFields::hub_activated_frame)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => "50rem", "maxWidth" => "70rem"],
                ["number" => 3, "minWidth" => "70rem"]
            ]
        );
        $this->addDescriptions($options);
        return $options;
    }
}
