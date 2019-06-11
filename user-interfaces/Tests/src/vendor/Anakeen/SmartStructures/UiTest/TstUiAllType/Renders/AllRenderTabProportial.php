<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Ui\RenderOptions;

class AllRenderTabProportial extends \Anakeen\Ui\DefaultEdit
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $options->document()->setTabPlacement(\Anakeen\Ui\DocumentRenderOptions::tabTopProportionalPlacement);
        $options->tab()->setTooltipLabel("{{label}}");
        return $options;
    }
}
