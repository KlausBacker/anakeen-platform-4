<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class DocumentController_getAttribute extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}
