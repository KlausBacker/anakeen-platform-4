<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEnum\Renders;

use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_enum as myAttributes;

class EnumRenderConfigEditHorizontal extends EnumRenderConfigEditButtons
{
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Enum Edit Horizontal";
    }
    
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        
        $options->enum()->setDisplay(\Dcp\Ui\EnumRenderOptions::horizontalDisplay);
        //$options->enum()->useOtherChoice(true);
        return $options;
    }
    
    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["tstenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/tstenumhorizontal.css";
        return $css;
    }
}
