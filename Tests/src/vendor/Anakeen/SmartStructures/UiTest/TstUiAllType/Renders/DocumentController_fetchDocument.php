<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class DocumentController_fetchDocument extends \Dcp\Ui\DefaultEdit
{

    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/userButton.css?ws=" . $version;
        return $cssReferences;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/DocumentController_fetchDocument.js?ws=" . $version;
        return $js;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        $options = parent::getOptions($document);
        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<span>  Fetcher le document </span><i class="fa fa-eye"></i>';
        $viewDoc->class = "mybtn_DocumentController_fetchDocument userButton";
        $viewDoc->windowWidth = "400px";
        $options->commonOption(myAttributes::test_ddui_all__title)->addButton($viewDoc);


        return $options;

    }
}
