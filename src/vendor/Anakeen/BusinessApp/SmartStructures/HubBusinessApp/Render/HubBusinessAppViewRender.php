<?php

namespace Anakeen\BusinessApp\SmartStructures\HubBusinessApp\Render;

class HubBusinessAppViewRender extends \Anakeen\Hub\SmartStructures\HubConfiguration\Render\HubConfigurationViewRender
{
    use THubBusinessAppRender;
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): \Anakeen\Ui\RenderOptions
    {
        $options = parent::getOptions($document);
        return $this->getCommonOptions($options);
    }
}
