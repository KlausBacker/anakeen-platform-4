<?php

namespace Anakeen\SmartStructures\UiTest\TstUiColor\Renders;

class ColorViewRender extends \Anakeen\Ui\DefaultView
{

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return __METHOD__;
    }


    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $css = parent::getCssReferences($document);
        $css["tstColor"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_color/color.css?ws=" . $version;
        return $css;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $js = parent::getJsReferences($document);
        $js["tstColor"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_color/color.js?ws=" . $version;
        return $js;
    }
}
