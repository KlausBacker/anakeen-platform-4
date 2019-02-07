<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderNoneArrayCollapseView extends \Anakeen\Ui\DefaultView
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute()->setCollapse(\Anakeen\Ui\ArrayRenderOptions::collapseNone);
        return $options;
    }
}
