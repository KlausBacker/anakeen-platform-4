<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderLeftLabel extends AllRenderConfigEdit
{

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::leftPosition);
        $options->arrayAttribute()->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::leftPosition);

        return $options;
    }
}
