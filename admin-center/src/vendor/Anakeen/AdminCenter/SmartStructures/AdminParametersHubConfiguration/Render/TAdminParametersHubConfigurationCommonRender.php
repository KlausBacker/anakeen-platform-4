<?php

namespace Anakeen\AdminCenter\SmartStructures\AdminParametersHubConfiguration\Render;

use SmartStructure\Fields\Hubconfigurationgeneric as HubConfigurationGenericFields;
use SmartStructure\Fields\Adminparametershubconfiguration as AdminParametersHubConfigurationFields;
use Anakeen\Ui\RenderAttributeVisibilities;

trait TAdminParametersHubConfigurationCommonRender
{
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, ?\SmartStructure\Mask $mask = null): \Anakeen\Ui\RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);

        $visibilities->setVisibility(HubConfigurationGenericFields::hge_fr_assets, RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubConfigurationGenericFields::hge_fr_identification, RenderAttributeVisibilities::HiddenVisibility);
        return $visibilities;
    }
}
