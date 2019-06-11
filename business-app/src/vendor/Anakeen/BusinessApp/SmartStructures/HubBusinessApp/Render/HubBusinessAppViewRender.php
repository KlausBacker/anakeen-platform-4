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
}
