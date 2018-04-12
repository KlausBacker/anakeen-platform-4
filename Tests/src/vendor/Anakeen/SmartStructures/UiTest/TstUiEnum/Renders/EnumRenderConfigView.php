<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEnum\Renders;

use SmartStructure\Attributes\Tst_ddui_enum as myAttributes;

class EnumRenderConfigView extends \Dcp\Ui\DefaultView
{
    
    public function getLabel(\Doc $document = null)
    {
        return "Enum View";
    }

    public function getOptions(\Doc $document)
    {
        $options= parent::getOptions(
            $document
        );

        $options->document()->setTabPlacement(\Dcp\Ui\DocumentRenderOptions::tabTopProportionalPlacement);
        return $options;
    }
    public function getCssReferences(\Doc $document = null)
    {
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        $css = parent::getCssReferences($document);
        $css["tstviewenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/testViewEnum.css?ws=".$version;
        $css["tstotherenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/testOtherEnum.css?ws=".$version;
        return $css;
    }
    public function getJsReferences(\Doc $document = null)
    {
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        $jsReferences = parent::getJsReferences($document);
        $jsReferences["tstviewenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/testViewEnum.js?ws=".$version;
        return $jsReferences;
    }
}
