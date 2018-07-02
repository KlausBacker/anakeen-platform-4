<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class setAttributeLabel extends \Dcp\Ui\DefaultView
{

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setAttributeLabel("Mon texte");

        return $options;

    }
}
