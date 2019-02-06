<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEnum\Renders;

use Anakeen\Core\ContextManager;
use Anakeen\Ui\RenderOptions;

class EnumRenderConfigView extends \Anakeen\Ui\DefaultView
{
    
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Enum View";
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document) : RenderOptions
    {
        $options= parent::getOptions(
            $document
        );

        $options->document()->setTabPlacement(\Anakeen\Ui\DocumentRenderOptions::tabTopProportionalPlacement);
        return $options;
    }
    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $css = parent::getCssReferences($document);
        $css["tstviewenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/testViewEnum.css?ws=".$version;
        $css["tstotherenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/testOtherEnum.css?ws=".$version;
        return $css;
    }
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $jsReferences = parent::getJsReferences($document);
        $jsReferences["tstviewenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/testViewEnum.js?ws=".$version;
        return $jsReferences;
    }
}
