<?php


namespace Anakeen\Hub\SmartStructures\HubInstanciation\Render;

use Anakeen\Ui\CommonRenderOptions;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Hubinstanciation as HubInstanciationFields;

class HubInstanciationEditRender extends \Anakeen\Ui\DefaultConfigEditRender
{
    use THubInstanciationCommonRender;
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setRowMinDefault(2);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setRowMaxLimit(2);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->disableRowAdd(true);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->disableRowDel(true);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setCollapse("none");

        $options->arrayAttribute(HubInstanciationFields::hub_instance_jsassets)->setRowMinDefault(0);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_cssassets)->setRowMinDefault(0);

        $options->text(HubInstanciationFields::hub_instance_title)->setMaxLength(50);
        $options->text(HubInstanciationFields::hub_instance_language)->setMaxLength(15);
        $options->frame(HubInstanciationFields::hub_security_frame)->setLabelPosition(CommonRenderOptions::nonePosition);



        $this->addDescriptions($options);

        return $options;
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @param \SmartStructure\Mask|null           $mask
     *
     * @return RenderAttributeVisibilities
     * @throws \Anakeen\Ui\Exception
     */
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(HubInstanciationFields::hub_instance_language, RenderAttributeVisibilities::StaticWriteVisibility);
        return $visibilities;
    }
}
