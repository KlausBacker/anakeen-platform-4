<?php

namespace Anakeen\BusinessApp\SmartStructures\HubBusinessApp\Render;

use Anakeen\Ui\RenderAttributeVisibilities;
use SmartStructure\Fields\Hubbusinessapp as HubBusinessAppFields;

class HubBusinessAppViewRender extends \Anakeen\Hub\SmartStructures\HubConfigurationVue\Render\HubConfigurationVueViewRender
{
    use THubBusinessAppRender;
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): \Anakeen\Ui\RenderOptions
    {
        $options = parent::getOptions($document);
        return $this->getCommonOptions($options);
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences($document);
        return $this->getCommonJSReferences($js);
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(HubBusinessAppFields::hba_icon_lib, RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubBusinessAppFields::hba_icon_image, RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubBusinessAppFields::hba_icon_html, RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubBusinessAppFields::hba_icon_type, RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubBusinessAppFields::hba_icon, RenderAttributeVisibilities::ReadWriteVisibility);
        return $visibilities;
    }
}
