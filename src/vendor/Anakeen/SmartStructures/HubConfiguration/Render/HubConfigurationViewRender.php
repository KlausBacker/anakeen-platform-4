<?php

namespace Anakeen\SmartStructures\HubConfiguration\Render;

use Dcp\Ui\RenderAttributeVisibilities;
use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\HubConfiguration as HubConfigurationFields;

class HubConfigurationViewRender extends \Anakeen\Ui\DefaultConfigViewRender
{
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(HubConfigurationFields::hub_language_code, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubConfigurationFields::hub_font_name, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubConfigurationFields::hub_icon_name, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        return $visibilities;
    }
}
