<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderCollapeArrayView extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute()->setCollapse(\Dcp\Ui\ArrayRenderOptions::collapseCollapsed);
        return $options;
    }
}