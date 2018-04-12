<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class AllRenderConfigView extends \Dcp\Ui\DefaultView
{

    public function getLabel(\Doc $document = null)
    {
        return "All View";
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        return $options;
    }
}
