<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderConfigEdit extends \Anakeen\Ui\DefaultEdit
{
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "All Edit";
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        // Inhibit enum toolips
        $options->enum()->setTranslations(array(
            "invertSelection" => "",
            "selectMessage" => ""
        ));
        $options->enum(myAttributes::test_ddui_all__enumlist)->setDisplay(\Anakeen\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_all__enumauto)->setDisplay(\Anakeen\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_all__enumvertical)->setDisplay(\Anakeen\Ui\EnumRenderOptions::verticalDisplay);
        $options->enum(myAttributes::test_ddui_all__enumhorizontal)->setDisplay(\Anakeen\Ui\EnumRenderOptions::horizontalDisplay);
        $options->enum(myAttributes::test_ddui_all__enumbool)->setDisplay(\Anakeen\Ui\EnumRenderOptions::boolDisplay);

        $options->enum(myAttributes::test_ddui_all__enumserverlist)->setDisplay(\Anakeen\Ui\EnumRenderOptions::listDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_all__enumserverauto)->setDisplay(\Anakeen\Ui\EnumRenderOptions::autocompletionDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_all__enumserververtical)->setDisplay(\Anakeen\Ui\EnumRenderOptions::verticalDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_all__enumserverhorizontal)->setDisplay(\Anakeen\Ui\EnumRenderOptions::horizontalDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_all__enumserverbool)->setDisplay(\Anakeen\Ui\EnumRenderOptions::boolDisplay)->useSourceUri(true);

        $options->enum(myAttributes::test_ddui_all__enumslist)->setDisplay(\Anakeen\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_all__enumsauto)->setDisplay(\Anakeen\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_all__enumsvertical)->setDisplay(\Anakeen\Ui\EnumRenderOptions::verticalDisplay);
        $options->enum(myAttributes::test_ddui_all__enumshorizontal)->setDisplay(\Anakeen\Ui\EnumRenderOptions::horizontalDisplay);

        $options->enum(myAttributes::test_ddui_all__enumsserverlist)->setDisplay(\Anakeen\Ui\EnumRenderOptions::listDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumsserverauto)->setDisplay(\Anakeen\Ui\EnumRenderOptions::autocompletionDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumsserververtical)->setDisplay(\Anakeen\Ui\EnumRenderOptions::verticalDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumsserverhorizontal)->setDisplay(\Anakeen\Ui\EnumRenderOptions::horizontalDisplay)->useSourceUri(false);


        return $options;
    }
}
