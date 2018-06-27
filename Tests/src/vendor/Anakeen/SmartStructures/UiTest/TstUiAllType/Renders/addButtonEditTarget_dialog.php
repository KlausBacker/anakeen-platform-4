<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class addButtonEditTarget_dialog extends \Dcp\Ui\DefaultEdit
{
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonJS.js?ws=" . $version;
        return $js;
    }

    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonCSS.css?ws=" . $version;
        return $cssReferences;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<span>  un button autre </span><i class="fa fa-eye"></i>';
        $viewDoc->url = "#action/my:myOptions";
        $viewDoc->target = "_dialog";
        $viewDoc->class = "mybtn mybtn-1";
        $viewDoc->windowWidth = "400px";
        $options->docid()->addButton($viewDoc);

        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<span>  un button autre2 </span><i class="fa fa-eye"></i>';
        $viewDoc->url = "https://fr.wikipedia.org/wiki/ {{value}} ";
        $viewDoc->target = "_dialog";
        $viewDoc->class = "myClass";
        $options->docid()->addButton($viewDoc);

        $cogButton = new \Dcp\Ui\ButtonOptions();
        $cogButton->htmlContent = '<span>  un button grands </span><i class="fa fa-eye"></i>';
        $options->text()->addButton($cogButton);

        $superButton = new \Dcp\Ui\ButtonOptions();
        $superButton->htmlContent = '<span>  un button grands </span><i class="fa fa-eye"></i>';
        $superButton->class = "myClass";
        $options->commonOption()->addButton($superButton);

        return $options;

    }
}
