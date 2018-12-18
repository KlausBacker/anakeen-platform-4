<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration\Render;

use Dcp\Ui\RenderAttributeVisibilities;
use Dcp\Ui\RenderOptions;

class HubConfigurationViewRender extends \Anakeen\Ui\DefaultConfigViewRender
{
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility("hub_language_code", \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility("hub_font_name", \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility("hub_icon_name", \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        return $visibilities;
    }
}
