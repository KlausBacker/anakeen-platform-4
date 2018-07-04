<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class setAutoCompleteHtmlLabel extends AllRenderConfigEdit
{

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setAutoCompleteHtmlLabel("Choisissez un code postal du <b>Pays</b>");

        return $options;

    }
}
