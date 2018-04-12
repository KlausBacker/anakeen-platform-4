<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class AllRenderTabLeft extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->document()->setTabPlacement(\Dcp\Ui\DocumentRenderOptions::tabLeftPlacement);
        return $options;
    }
}
