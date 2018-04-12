<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEnum\Renders;

use SmartStructure\Attributes\Tst_ddui_enum as myAttributes;

class EnumRenderConfigEditHorizontal extends EnumRenderConfigEditButtons
{
    public function getLabel(\Doc $document = null)
    {
        return "Enum Edit Horizontal";
    }
    
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        
        $options->enum()->setDisplay(\Dcp\Ui\EnumRenderOptions::horizontalDisplay);
        //$options->enum()->useOtherChoice(true);
        return $options;
    }
    
    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["tstenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/tstenumhorizontal.css";
        return $css;
    }
}
