<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Ui\RenderAttributeNeeded;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderAllNeeded extends \Anakeen\Ui\DefaultEdit
{
    public function getNeeded(
        \Anakeen\Core\Internal\SmartElement $document,
        \SmartStructure\Mask $mask = null
    ): RenderAttributeNeeded {
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
