<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Dcp\Ui\RenderAttributeVisibilities;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderVisibilityHidden extends \Dcp\Ui\DefaultView
{
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null) : RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $attrs = $document->getFieldAttributes();
        foreach ($attrs as $attrid => $attr) {

            $visibilities->setVisibility($attrid, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);

        }
        return $visibilities;
    }
}