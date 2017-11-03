<?php
/*
 * @author Anakeen
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
        

        
        return $options;
    }
}

class AllRenderConfigView extends \Dcp\Ui\DefaultView
{
    
    public function getLabel(\Doc $document = null)
    {
        return "All View";
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        return $options;
    }
}
class AllRenderCollapseView extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->frame()->setCollapse(true);

        return $options;
    }

}

class AllRenderNoneCollapseView extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->frame()->setCollapse(\Dcp\Ui\FrameRenderOptions::collapseNone);

        return $options;
    }

}

class AllRenderNoneArrayCollapseView extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute()->setCollapse(\Dcp\Ui\ArrayRenderOptions::collapseNone);
        return $options;
    }
}
class AllRenderCollapeArrayView extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute()->setCollapse(\Dcp\Ui\ArrayRenderOptions::collapseCollapsed);
        return $options;
    }
}
class AllRenderTabLeft extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->document()->setTabPlacement(\Dcp\Ui\DocumentRenderOptions::tabLeftPlacement);
        return $options;
    }
}
class AllRenderTabTopScroll extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->document()->setTabPlacement(\Dcp\Ui\DocumentRenderOptions::tabTopScrollPlacement);
        return $options;
    }
}
class AllRenderTabTopFix extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->document()->setTabPlacement(\Dcp\Ui\DocumentRenderOptions::tabTopFixPlacement);
        return $options;
    }
}
class AllRenderTabProportial extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->document()->setTabPlacement(\Dcp\Ui\DocumentRenderOptions::tabTopProportionalPlacement);
        return $options;
    }
}
class AllRenderAllNeeded extends \Dcp\Ui\DefaultEdit
{
    public function getNeeded(\Doc $document)
    {
        $need=parent::getNeeded($document);
        $attrs=$document->getNormalAttributes();
        foreach ($attrs as $attrid => $attr) {
        if ($attr->type !== "array") {
            $need->setNeeded($attrid, true);
        }
        }
        return $need;
    }
}
class AllRenderVisibilityRead extends \Dcp\Ui\DefaultEdit
{
    public function getVisibilities(\Doc $document)
    {
        $visibilities =parent::getVisibilities($document);
        $attrs=$document->getFieldAttributes();
        foreach ($attrs as $attrid => $attr) {

                $visibilities->setVisibility($attrid, \Dcp\Ui\RenderAttributeVisibilities::ReadOnlyVisibility);

        }
        return $visibilities;
    }
}
class AllRenderVisibilityStatic extends \Dcp\Ui\DefaultEdit
{
    public function getVisibilities(\Doc $document)
    {
        $visibilities =parent::getVisibilities($document);
        $attrs=$document->getFieldAttributes();
        foreach ($attrs as $attrid => $attr) {

                $visibilities->setVisibility($attrid, \Dcp\Ui\RenderAttributeVisibilities::StaticWriteVisibility);

        }
        return $visibilities;
    }
}
class AllRenderVisibilityHidden extends \Dcp\Ui\DefaultView
{
    public function getVisibilities(\Doc $document)
    {
        $visibilities =parent::getVisibilities($document);
        $attrs=$document->getFieldAttributes();
        foreach ($attrs as $attrid => $attr) {

                $visibilities->setVisibility($attrid, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);

        }
        return $visibilities;
    }
}


class AllRenderSetInput extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setInputTooltip("<b>Veuillez saisir une valeur</b>");
        return $options;
    }
}
class AllRenderNotification extends \Dcp\Ui\DefaultView
{

    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        $jsReferences = parent::getJsReferences($document);
        $jsReferences["tstNotification"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testNotifications.js?ws=".$version;
        return $jsReferences;
    }
}


class AllRenderCssColor extends AllRenderConfigEdit
{

    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstNotification"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testColor.css?ws=".$version;
        return $cssReferences;
    }
}

class AllRenderButtons extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $viewDoc=new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent='<i class="fa fa-eye"></i>';
        $viewDoc->url=sprintf("api/v1/documents/{{value}}.html" );
        $viewDoc->target="_dialog";
        $viewDoc->windowWidth="400px";

        $options->docid()->addButton($viewDoc);


        $cogButton=new \Dcp\Ui\ButtonOptions();
        $cogButton->htmlContent='<i class="fa fa-cog"></i>';
        $options->text()->addButton($cogButton);



        $superButton=new \Dcp\Ui\ButtonOptions();
        $superButton->htmlContent='<i class="fa fa-superpowers"></i>';
        $options->commonOption()->addButton($superButton);


        $options->docid()->addButton($superButton);
        return $options;
    }
}


class AllRenderLeftLabel extends AllRenderConfigEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::leftPosition);
        $options->arrayAttribute()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::leftPosition);

        return $options;
    }
}


class AllRenderUpLabel extends AllRenderConfigEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->arrayAttribute()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);

        return $options;
    }
}


class AllRenderAutoLabel extends AllRenderConfigEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::autoPosition);
        $options->arrayAttribute()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::autoPosition);

        return $options;
    }
}