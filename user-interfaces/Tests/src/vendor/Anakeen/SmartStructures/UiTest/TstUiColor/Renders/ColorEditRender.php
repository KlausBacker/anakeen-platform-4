<?php

namespace Anakeen\SmartStructures\UiTest\TstUiColor\Renders;

use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Tst_ddui_color as myAttribute;

class ColorEditRender extends \Anakeen\Ui\DefaultEdit
{

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return __METHOD__;
    }

    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $css = parent::getCssReferences($document);
        $css["tstColor"] = "/TEST_DOCUMENT_SELENIUM/Family/tst_ddui_color/color.css?ws=" . $version;
        return $css;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $js = parent::getJsReferences($document);
        $js["tstColor"] = "/TEST_DOCUMENT_SELENIUM/Family/tst_ddui_color/color.js?ws=" . $version;
        return $js;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);


        $options->color(myAttribute::tst_color2)->setKendoColorConfiguration([
            "palette" => ["#ff0000", "#00ff00", "#0000ff"],
            "tileSize" => [
                "width" => 100,
                "height" => 50
            ]
        ]);

        $options->color(myAttribute::tst_color3)->setKendoColorConfiguration([
            "palette" => ["#00ffff", "#ff00ff", "#ffff00"],
            "tileSize" => [
                "width" => 100,
                "height" => 50
            ],
            "buttons" => true,
            "messages" => [
                "apply" => "Update",
                "cancel" => "Discard"
            ]
        ]);


        $options->color(myAttribute::tst_colors1)->setKendoColorConfiguration([
            "buttons" => true,
            "messages" => [
                "apply" => "Mettre à jour la couleur",
                "cancel" => "Annuler la colorisation"
            ]
        ]);

        $options->color(myAttribute::tst_colors2)->setKendoColorConfiguration([
            "palette" => ["#ff0000", "#00ff00", "#0000ff", "#00ffff", "#ff00ff", "#ffff00"],
            "columns" => 3,
            "tileSize" => [
                "width" => 100,
                "height" => 50
            ]
        ]);

        $options->color(myAttribute::tst_colors3)->setKendoColorConfiguration([
            "palette" => [
                "#ff0000", "#00ff00", "#0000ff", "#000000",
                "#00ffff", "#ff00ff", "#ffff00", "#ffffff",
                "#773f70", "#2f7730", "#a04f77", "#a0778f"],
            "columns" => 4,
            "tileSize" => [
                "width" => 70,
                "height" => 35
            ]
        ]);



        return $options;
    }
}

