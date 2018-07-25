<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Dcp\Ui\RenderAttributeNeeded;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderAllNeeded extends \Dcp\Ui\DefaultEdit
{
    public function getNeeded(\Anakeen\Core\Internal\SmartElement $document) : RenderAttributeNeeded
    {
        $need = parent::getNeeded($document);
        $attrs = $document->getNormalAttributes();
        foreach ($attrs as $attrid => $attr) {
            if ($attr->type !== "array") {
                $need->setNeeded($attrid, true);
            }
        }
        return $need;
    }
}
