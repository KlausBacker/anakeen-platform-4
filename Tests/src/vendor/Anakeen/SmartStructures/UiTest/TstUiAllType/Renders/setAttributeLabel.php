<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class setAttributeLabel extends \Dcp\Ui\DefaultView
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setAttributeLabel("Mon texte");

        return $options;

    }
}
