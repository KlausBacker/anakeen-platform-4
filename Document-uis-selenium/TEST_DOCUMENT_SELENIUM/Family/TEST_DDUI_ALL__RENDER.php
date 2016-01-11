<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Created by PhpStorm.
 * User: eric
 * Date: 16/09/14
 * Time: 11:22
 */

namespace Dcp\Test\Ddui;

use Dcp\AttributeIdentifiers\TST_DDUI_ALLTYPE as myAttributes;

class AllRenderConfigEdit extends \Dcp\Ui\DefaultEdit
{
    public function getLabel(\Doc $document = null)
    {
        return "All Edit";
    }
    
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        
        $options->document()->setTabPlacement(\Dcp\Ui\DocumentRenderOptions::tabTopProportionalPlacement);
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
        
        $options->enum(myAttributes::test_ddui_all__enumserverlist)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumserverauto)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumserververtical)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumserverhorizontal)->setDisplay(\Dcp\Ui\EnumRenderOptions::horizontalDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumserverbool)->setDisplay(\Dcp\Ui\EnumRenderOptions::boolDisplay)->useSourceUri(false);
        
        $options->enum(myAttributes::test_ddui_all__enumslist)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay);
        $options->enum(myAttributes::test_ddui_all__enumsauto)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay);
        $options->enum(myAttributes::test_ddui_all__enumsvertical)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay);
        $options->enum(myAttributes::test_ddui_all__enumshorizontal)->setDisplay(\Dcp\Ui\EnumRenderOptions::horizontalDisplay);
        
        $options->enum(myAttributes::test_ddui_all__enumsserverlist)->setDisplay(\Dcp\Ui\EnumRenderOptions::listDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumsserverauto)->setDisplay(\Dcp\Ui\EnumRenderOptions::autocompletionDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumsserververtical)->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay)->useSourceUri(false);
        $options->enum(myAttributes::test_ddui_all__enumsserverhorizontal)->setDisplay(\Dcp\Ui\EnumRenderOptions::horizontalDisplay)->useSourceUri(false);
        
        $options->arrayAttribute()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        
        return $options;
    }
}

class AllRenderConfigView extends \Dcp\Ui\DefaultView
{
    
    public function getLabel(\Doc $document = null)
    {
        return "All View";
    }
}
