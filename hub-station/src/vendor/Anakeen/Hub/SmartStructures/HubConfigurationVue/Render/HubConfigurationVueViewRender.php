<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationVue\Render;

use Anakeen\Hub\SmartStructures\HubConfiguration\Render\HubConfigurationViewRender;
use Anakeen\Ui\RenderOptions;

class HubConfigurationVueViewRender extends HubConfigurationViewRender
{
    use THubConfigurationVueCommonRender;

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $this->commonRenderOptions($options);
        return $options;
    }
}
