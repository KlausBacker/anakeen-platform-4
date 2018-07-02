<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class setTranslation extends \Dcp\Ui\DefaultEdit
{
    //vérification lors de la modification de l'attribut
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/setTranslation.js?ws=" . $version;
        return $js;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->commonOption(myAttributes::test_ddui_all__integer)->setTranslations([
            "decreaseLabel" => "5 kilos de moins",
            "increaseLabel" => "50 kilos de plus"
        ]);
        $options->commonOption()->setTranslations(
            array(
                "closeErrorMessage" => ___("close me please", "my"),
                "deleteLabel" => ___("kill it", "my")
            ));

        $options->arrayAttribute("test_ddui_all__file_array")->setTranslations([
            "tooltipLabel" => "Choisissez un plan",
        ]);
        $options->file("test_ddui_all__file")->setTranslations([
            "tooltipLabel" => "Choisissez un plan",
        ]);

        return $options;

    }
}
