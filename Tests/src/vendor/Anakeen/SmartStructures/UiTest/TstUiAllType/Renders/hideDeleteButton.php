<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class hideDeleteButton extends AllRenderConfigEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->commonOption()->displayDeleteButton(false);

        return $options;
    }
}
