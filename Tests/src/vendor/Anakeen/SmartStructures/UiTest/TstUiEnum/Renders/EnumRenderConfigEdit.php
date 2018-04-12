<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEnum\Renders;

use SmartStructure\Attributes\Tst_ddui_enum as myAttributes;

class EnumRenderConfigEdit extends EnumRenderConfigEditDefault
{
    public function getLabel(\Doc $document = null)
    {
        return "Enum Edit";
    }
    
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->enum()->setTranslations(array(
            "invertSelection" => "",
            "selectMessage" => ""
        ));
        // ----------------------------
        // Direct
        $options->enum(myAttributes::test_ddui_enum__enumcountry)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumtext)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumnumber)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumbool)->setDisplay(\Dcp\Ui\EnumRenderOptions::boolDisplay);
        
        $options->enum(myAttributes::test_ddui_enum__enumscountry)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumstext)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumsnumber)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay);
        // ----------------------------
        // Server
        $options->enum(myAttributes::test_ddui_enum__srvcountry)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_enum__srvtext)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_enum__srvnumber)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_enum__srvbool)->setDisplay(\Dcp\Ui\EnumRenderOptions::boolDisplay)->useSourceUri(true);
        
        $options->enum(myAttributes::test_ddui_enum__srvscountry)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_enum__srvstext)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay)->useSourceUri(true);
        $options->enum(myAttributes::test_ddui_enum__srvsnumber)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay)->useSourceUri(true);
        // ----------------------------
        // Array direct
        $options->enum(myAttributes::test_ddui_enum__enumtown_array)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumtext_array)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumnumber_array)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumbool_array)->setDisplay(\Dcp\Ui\EnumRenderOptions::boolDisplay);
        
        $options->enum(myAttributes::test_ddui_enum__enumstown_array)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumstext_array)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_enum__enumsnumber_array)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay);
        
        $options->arrayAttribute()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        
        return $options;
    }
}
