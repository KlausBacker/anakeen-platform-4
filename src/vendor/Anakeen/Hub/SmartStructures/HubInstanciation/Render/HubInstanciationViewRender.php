<?php


namespace Anakeen\Hub\SmartStructures\HubInstanciation\Render;

use Dcp\Ui\RenderAttributeVisibilities;
use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Hubinstanciation as HubInstanciationFields;

class HubInstanciationViewRender extends \Anakeen\Ui\DefaultConfigViewRender
{
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        return $visibilities;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setRowMinLimit(1);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setCollapse("none");

        return $options;
    }
}
