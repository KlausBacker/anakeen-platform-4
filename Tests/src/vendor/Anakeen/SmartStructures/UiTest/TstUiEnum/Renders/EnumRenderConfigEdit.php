<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEnum\Renders;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_enum as myAttributes;

class EnumRenderConfigEdit extends EnumRenderConfigEditDefault
{
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Enum Edit";
    }
    
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->enum()->setTranslations(array(
            "invertSelection" => "",
            "selectMessage" => ""
        ));
        // ----------------------------
        // Direct
        $options->enum(myAttributes::test_ddui_enum__enumcountry)->setDisplay(\Anakeen\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumtext)->setDisplay(\Anakeen\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumnumber)->setDisplay(\Anakeen\Ui\EnumRenderOptions::verticalDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumbool)->setDisplay(\Anakeen\Ui\EnumRenderOptions::boolDisplay);
        
        $options->enum(myAttributes::test_ddui_enum__enumscountry)->setDisplay(\Anakeen\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumstext)->setDisplay(\Anakeen\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumsnumber)->setDisplay(\Anakeen\Ui\EnumRenderOptions::verticalDisplay);
        // ----------------------------
        // Server
        $options->enum(myAttributes::test_ddui_enum__srvcountry)->setDisplay(\Anakeen\Ui\EnumRenderOptions::listDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_enum__srvtext)->setDisplay(\Anakeen\Ui\EnumRenderOptions::autocompletionDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_enum__srvnumber)->setDisplay(\Anakeen\Ui\EnumRenderOptions::verticalDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_enum__srvbool)->setDisplay(\Anakeen\Ui\EnumRenderOptions::boolDisplay)->useSourceUri(true);
        
        $options->enum(myAttributes::test_ddui_enum__srvscountry)->setDisplay(\Anakeen\Ui\EnumRenderOptions::listDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_enum__srvstext)->setDisplay(\Anakeen\Ui\EnumRenderOptions::autocompletionDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_enum__srvsnumber)->setDisplay(\Anakeen\Ui\EnumRenderOptions::verticalDisplay)->useSourceUri(true);
        // ----------------------------
        // Array direct
        $options->enum(myAttributes::test_ddui_enum__enumtown_array)->setDisplay(\Anakeen\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumtext_array)->setDisplay(\Anakeen\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumnumber_array)->setDisplay(\Anakeen\Ui\EnumRenderOptions::verticalDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumbool_array)->setDisplay(\Anakeen\Ui\EnumRenderOptions::boolDisplay);
        
        $options->enum(myAttributes::test_ddui_enum__enumstown_array)->setDisplay(\Anakeen\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumstext_array)->setDisplay(\Anakeen\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumsnumber_array)->setDisplay(\Anakeen\Ui\EnumRenderOptions::verticalDisplay);
        
        $options->arrayAttribute()->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::upPosition);
        
        return $options;
    }
}
