<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class AllRenderUpLabel extends AllRenderConfigEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->arrayAttribute()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);

        return $options;
    }
}
