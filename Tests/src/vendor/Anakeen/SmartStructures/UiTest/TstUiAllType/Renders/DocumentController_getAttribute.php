<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class DocumentController_getAttribute extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);


        return $options;

    }
}