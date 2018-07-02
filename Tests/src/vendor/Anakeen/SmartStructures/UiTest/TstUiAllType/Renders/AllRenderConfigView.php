<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderConfigView extends \Dcp\Ui\DefaultView
{

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "All View";
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        return $options;
    }
}
