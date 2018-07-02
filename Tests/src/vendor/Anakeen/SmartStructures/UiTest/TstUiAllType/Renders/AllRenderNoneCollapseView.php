<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderNoneCollapseView extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        $options->frame()->setCollapse(\Dcp\Ui\FrameRenderOptions::collapseNone);

        return $options;
    }

}
