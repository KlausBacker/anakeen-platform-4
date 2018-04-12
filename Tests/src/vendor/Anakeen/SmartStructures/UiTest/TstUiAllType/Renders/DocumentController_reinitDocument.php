<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class DocumentController_reinitDocument extends \Dcp\Ui\DefaultEdit
{

    public function getCssReferences(\Doc $document = null)
    {
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/userButton.css?ws=" . $version;
        return $cssReferences;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/DocumentController_reinitDocument.js?ws=" . $version;
        return $js;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options = parent::getOptions($document);
        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<span>  Reinitialiser le document </span><i class="fa fa-eye"></i>';
        $viewDoc->class = "mybtn_DocumentController_reinitDocument userButton";
        $viewDoc->windowWidth = "400px";
        $options->commonOption(myAttributes::test_ddui_all__title)->addButton($viewDoc);


        return $options;

    }
}
