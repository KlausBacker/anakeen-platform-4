<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderConfigView extends \Anakeen\Ui\DefaultView
{

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "All View";
    }


}
