<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class AllRenderNoneCollapseView extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->frame()->setCollapse(\Dcp\Ui\FrameRenderOptions::collapseNone);

        return $options;
    }

}
