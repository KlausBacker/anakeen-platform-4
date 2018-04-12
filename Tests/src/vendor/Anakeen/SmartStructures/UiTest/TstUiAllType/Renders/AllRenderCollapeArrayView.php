<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class AllRenderCollapeArrayView extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute()->setCollapse(\Dcp\Ui\ArrayRenderOptions::collapseCollapsed);
        return $options;
    }
}
