<?php

namespace Anakeen\SmartStructures\HubConfiguration\Render;

use Dcp\Ui\RenderAttributeVisibilities;
use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Hubconfiguration as HubConfigurationFields;

class HubConfigurationEditRender extends \Anakeen\Ui\DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);
        $options->arrayAttribute(HubConfigurationFields::hub_titles)->setRowMinLimit(1);
        return $options;
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(HubConfigurationFields::hub_language_code, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubConfigurationFields::hub_font_name, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubConfigurationFields::hub_icon_name, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        return $visibilities;
    }
}
