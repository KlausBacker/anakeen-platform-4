<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class addButtonConsTarget_dialog extends \Anakeen\Ui\DefaultView
{
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "/TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonJS.js?ws=" . $version;
        return $js;
    }

    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "/TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonCSS.css?ws=" . $version;
        return $cssReferences;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $viewDoc = new \Anakeen\Ui\ButtonOptions();
        $viewDoc->htmlContent = ' <p> un bouton qui contient une balise p</p>';
        $viewDoc->url = "https://fr.wikipedia.org/wiki/ {{value}} ";
        $viewDoc->target = "_dialog";
        $viewDoc->class = "mybtn mybtn-1";
        $viewDoc->windowWidth = "400px";
        $options->docid()->addButton($viewDoc);

        $viewDoc = new \Anakeen\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<span>  un button autre2 </span><i class="fa fa-eye"></i>';
        $viewDoc->url = "https://fr.wikipedia.org/wiki/ {{value}} ";
        $viewDoc->target = "_dialog";
        $viewDoc->class = "myClass";
        $options->docid()->addButton($viewDoc);

        $cogButton = new \Anakeen\Ui\ButtonOptions();
        $cogButton->htmlContent = '<span>  un button grands </span><i class="fa fa-eye"></i>';
        $options->text()->addButton($cogButton);

        $superButton = new \Anakeen\Ui\ButtonOptions();
        $superButton->htmlContent = '<p> un bouton qui contient une balise p</p>';
        $superButton->class = "myClass";
        $options->commonOption()->addButton($superButton);

        return $options;

    }
}
