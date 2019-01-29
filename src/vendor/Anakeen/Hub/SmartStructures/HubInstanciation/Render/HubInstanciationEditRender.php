<?php


namespace Anakeen\Hub\SmartStructures\HubInstanciation\Render;

use Dcp\Ui\CommonRenderOptions;
use Dcp\Ui\RenderAttributeVisibilities;
use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Hubinstanciation as HubInstanciationFields;

class HubInstanciationEditRender extends \Anakeen\Ui\DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setRowMinLimit(1);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setCollapse("none");
        $options->text(HubInstanciationFields::hub_instance_title)->setMaxLength(50);
        $options->text(HubInstanciationFields::hub_language)->setMaxLength(15);
        $options->frame(HubInstanciationFields::hub_security_frame)->setLabelPosition(CommonRenderOptions::nonePosition);

        return $options;
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(HubInstanciationFields::hub_language_code, RenderAttributeVisibilities::HiddenVisibility);
        return $visibilities;
    }

}