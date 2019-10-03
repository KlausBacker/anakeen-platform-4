<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class setTranslation extends \Anakeen\Ui\DefaultEdit
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
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
            )
        );

        $options->file("test_ddui_all__file_array")->setTranslations([
            "tooltipLabel" => "Choisissez un plan",
        ]);
        $options->file("test_ddui_all__file")->setTranslations([
            "tooltipLabel" => "Choisissez un plan",
        ]);


        $options->arrayAttribute("test_ddui_all__array_files")->setTranslations([
            "dragLine" => "Déplacer la rangée s'il vous plaît",
            "selectLine" => "Sélectionner la rangée",
            "deleteLine" => "Supprimer la rangée"
        ]);

        return $options;
    }
}
