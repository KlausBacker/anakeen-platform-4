<?php


namespace Anakeen\Hub\SmartStructures\HubConfigurationVue\Render;

use Anakeen\Hub\SmartStructures\HubConfiguration\Render\HubConfigurationEditRender;
use Anakeen\Ui\RenderOptions;

class HubConfigurationVueEditRender extends HubConfigurationEditRender
{
    use THubConfigurationVueCommonRender;
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $this->commonRenderOptions($options);

        return $options;
    }
}
