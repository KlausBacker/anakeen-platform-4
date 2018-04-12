<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEnum\Renders;

use SmartStructure\Attributes\Tst_ddui_enum as myAttributes;

class EnumRenderConfigEditVertical extends EnumRenderConfigEditButtons
{
    public function getLabel(\Doc $document = null)
    {
        return "Enum Edit Vertical";
    }
    
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        
        $options->enum()->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay);
        //$options->enum()->useOtherChoice(true);
        return $options;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["tstenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/tstenumvertical.css";
        return $css;
    }
}
