<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class AllRenderNoneLabel extends AllRenderConfigEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        $options->arrayAttribute()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        $options->tab()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::autoPosition);

        return $options;
    }
}
