<?php


namespace Anakeen\Hub\SmartStructures\HubConfigurationGeneric\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Hub\SmartStructures\HubConfigurationVue\Render\HubConfigurationVueViewRender;
use Anakeen\Ui\RenderOptions;

class HubConfigurationGenericViewRender extends HubConfigurationVueViewRender
{
    use THubConfigurationGenericCommonRender;

    public function getOptions(SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);
        $this->addDescriptions($options);
        return $options;
    }
}
