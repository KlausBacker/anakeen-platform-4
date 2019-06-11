<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Ui\RenderAttributeVisibilities;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderVisibilityHidden extends \Anakeen\Ui\DefaultView
{
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null) : RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $attrs = $document->getFieldAttributes();
        foreach ($attrs as $attrid => $attr) {

            $visibilities->setVisibility($attrid, \Anakeen\Ui\RenderAttributeVisibilities::HiddenVisibility);

        }
        return $visibilities;
    }
}
