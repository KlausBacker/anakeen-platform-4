<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class setTemplate extends \Dcp\Ui\DefaultEdit
{

    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/setTemplate.css?ws=" . $version;
        return $cssReferences;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/setTemplate.js?ws=" . $version;
        return $js;
    }


    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        $options->text("test_ddui_all__title")->setLabelPosition("none");
        $options->text("test_ddui_all__title")->setTemplate(
            '<div class="shadow gradient customGradient">
        <p>Mon label est : {{attributes.test_ddui_all__title.label}} </p> 
        <p>mon attributeId est : {{attributes.test_ddui_all__title.id}} </p> 
        <p>Ma valeur est : {{attributes.test_ddui_all__title.attributeValue.value}} </p> 
        <p> mon designer est toto</p>
        </div>'
        );


        $options->money()->setTemplate("<h2>Des sous</h2>");
        $options->int()->setTemplate("<h2>Des entiers</h2>");

        $options->text()->setTemplate("<h2>Des textes</h2>");
        $options->arrayAttribute("test_ddui_all__array_account")->setTemplate(file_get_contents(__DIR__ . "/Templates/myInfoArray.mustache"));
        $options->text("test_ddui_all__longtext")->setTemplate(file_get_contents(__DIR__ . "/Templates/myInfo.mustache"));
        $options->account("test_ddui_all__account_multiple")->setTemplate(file_get_contents(__DIR__ . "/Templates/myInfoMultiple.mustache"));

        return $options;

    }
}
