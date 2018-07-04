<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderConfigEdit extends \Dcp\Ui\DefaultEdit
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
        $options->enum(myAttributes::test_ddui_all__enumlist)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_all__enumauto)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_all__enumvertical)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay);
        $options->enum(myAttributes::test_ddui_all__enumhorizontal)->setDisplay(\Dcp\Ui\EnumRenderOptions::horizontalDisplay);
        $options->enum(myAttributes::test_ddui_all__enumbool)->setDisplay(\Dcp\Ui\EnumRenderOptions::boolDisplay);

        $options->enum(myAttributes::test_ddui_all__enumserverlist)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_all__enumserverauto)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_all__enumserververtical)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_all__enumserverhorizontal)->setDisplay(\Dcp\Ui\EnumRenderOptions::horizontalDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_all__enumserverbool)->setDisplay(\Dcp\Ui\EnumRenderOptions::boolDisplay)->useSourceUri(true);

        $options->enum(myAttributes::test_ddui_all__enumslist)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_all__enumsauto)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_all__enumsvertical)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay);
        $options->enum(myAttributes::test_ddui_all__enumshorizontal)->setDisplay(\Dcp\Ui\EnumRenderOptions::horizontalDisplay);

        $options->enum(myAttributes::test_ddui_all__enumsserverlist)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumsserverauto)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumsserververtical)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumsserverhorizontal)->setDisplay(\Dcp\Ui\EnumRenderOptions::horizontalDisplay)->useSourceUri(false);

        $options->htmltext()->useCkInline(true);

        return $options;
    }
}
