<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEnum\Renders;

use SmartStructure\Attributes\Tst_ddui_enum as myAttributes;

class EnumRenderConfigEditDefault extends \Dcp\Ui\DefaultEdit
{
    public function getLabel(\Doc $document = null)
    {
        return "Enum Edit Default";
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->document()->setTabPlacement(
            \Dcp\Ui\DocumentRenderOptions::tabTopProportionalPlacement
        );
        return $options;
    }


    public function getCssReferences(\Doc $document = null)
    {
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        $css = parent::getCssReferences($document);
        $css["tstotherenum"] = "TEST_DOCUMENT_SELENIUM/Layout/testOtherEnum.css?ws=".$version;
        return $css;
    }
}
