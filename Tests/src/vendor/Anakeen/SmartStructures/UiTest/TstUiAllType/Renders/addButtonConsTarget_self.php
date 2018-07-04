<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class addButtonConsTarget_self extends \Dcp\Ui\DefaultView
{
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonJS.js?ws=" . $version;
        return $js;
    }

    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonCSS.css?ws=" . $version;
        return $cssReferences;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<i class="fa fa-eye"></i>';
        $viewDoc->url = sprintf("api/v1/documents/{{value}}.html");
        $viewDoc->target = "_self";
        $viewDoc->windowWidth = "400px";
        $options->docid()->addButton($viewDoc);

        $cogButton = new \Dcp\Ui\ButtonOptions();
        $cogButton->htmlContent = '<i class="fa fa-cog"></i>';
        $options->text()->addButton($cogButton);

        $superButton = new \Dcp\Ui\ButtonOptions();
        $superButton->htmlContent = '<i class="fa fa-superpowers"></i>';
        $options->commonOption()->addButton($superButton);
        $options->commonOption()->addButton($superButton);


        return $options;

    }
}
