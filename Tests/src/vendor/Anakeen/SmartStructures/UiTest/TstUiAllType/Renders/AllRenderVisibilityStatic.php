<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderVisibilityStatic extends \Dcp\Ui\DefaultEdit
{
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document)
    {
        $visibilities = parent::getVisibilities($document);
        $attrs = $document->getFieldAttributes();
        foreach ($attrs as $attrid => $attr) {

            $visibilities->setVisibility($attrid, \Dcp\Ui\RenderAttributeVisibilities::StaticWriteVisibility);

        }
        return $visibilities;
    }
}