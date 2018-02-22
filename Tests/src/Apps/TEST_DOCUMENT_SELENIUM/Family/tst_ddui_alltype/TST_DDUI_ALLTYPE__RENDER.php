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

        $options->htmltext()->useCkInline(true);

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
        $need = parent::getNeeded($document);
        $attrs = $document->getNormalAttributes();
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
        $visibilities = parent::getVisibilities($document);
        $attrs = $document->getFieldAttributes();
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
        $visibilities = parent::getVisibilities($document);
        $attrs = $document->getFieldAttributes();
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
        $visibilities = parent::getVisibilities($document);
        $attrs = $document->getFieldAttributes();
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
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $jsReferences = parent::getJsReferences($document);
        $jsReferences["tstNotification"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testNotifications.js?ws=" . $version;
        return $jsReferences;
    }
}


class AllRenderCssColor extends AllRenderConfigEdit
{

    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstNotification"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testColor.css?ws=" . $version;
        return $cssReferences;
    }
}

class AllRenderButtons extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<i class="fa fa-eye"></i>';
        $viewDoc->url = sprintf("api/v1/documents/{{value}}.html");
        $viewDoc->target = "_dialog";
        $viewDoc->windowWidth = "400px";

        $options->docid()->addButton($viewDoc);


        $cogButton = new \Dcp\Ui\ButtonOptions();
        $cogButton->htmlContent = '<i class="fa fa-cog"></i>';
        $options->text()->addButton($cogButton);


        $superButton = new \Dcp\Ui\ButtonOptions();
        $superButton->htmlContent = '<i class="fa fa-superpowers"></i>';
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

class AllRenderNoneLabel extends AllRenderConfigEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        $options->arrayAttribute()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        $options->tab()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::autoPosition);

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

class AllRenderShowEmpty extends AllRenderConfigView
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->showEmptyContent("Aucune information");
        $options->file()->showEmptyContent("Aucun fichier n'a été enregistré");
        $options->image()->showEmptyContent("Aucune image n'a été fournie");
        $options->color()->showEmptyContent('<b style="color:green">Aucune couleur à afficher même en vert</b>');
        $options->date()->showEmptyContent("Aucune date à afficher");
        $options->time()->showEmptyContent("Pas le temps");
        $options->timestamp()->showEmptyContent("<b><i>J'ai dit pas le temps ni pas de date non plus</i></b>");
        $options->arrayAttribute()->showEmptyContent("<h1>Aucune valeur dans ce tableau</h1>");


        $options->tab()->showEmptyContent('<h1 style="color:blue">Aucune valeur dans cet onglet</h1>');
        $options->frame()->showEmptyContent('<h1 style="color:red">Aucune valeur dans ce cadre</h1>');
        // Need to reset showEmpty to override commonOption and type options
        $options->arrayAttribute(myAttributes::test_ddui_all__array_misc)->showEmptyContent(null);
        $options->color(myAttributes::test_ddui_all__color_array)->showEmptyContent(null);
        $options->password(myAttributes::test_ddui_all__password_array)->showEmptyContent(null);


        $options->arrayAttribute(myAttributes::test_ddui_all__array_files)->showEmptyContent(null);
        $options->file(myAttributes::test_ddui_all__file_array)->showEmptyContent(null);
        $options->image(myAttributes::test_ddui_all__image_array)->showEmptyContent(null);
        $options->frame(myAttributes::test_ddui_all__frame_files)->showEmptyContent(null);
        return $options;
    }
}

class hideDeleteButton extends AllRenderConfigEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->commonOption()->displayDeleteButton(false);

        return $options;
    }
}

class setLinkTarget_self extends AllRenderConfigView
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonJS.js?ws=" . $version;
        return $js;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $linkOptionAccount = new \Dcp\ui\htmlLinkOptions();
        $linkOptionAccount->target = "_self";
        $linkOptionAccount->title = "Mon test {{value}} {{displayValue}}";
        $linkOptionAccount->url = "api/v1/documents/{{value}}/views/!defaultEdition.html";

        $linkOptionImage = new \Dcp\ui\htmlLinkOptions();
        $linkOptionImage->target = "_self";
        $linkOptionImage->title = ' <h3><img src="{{thumbnail}}&size=100"/>{{displayValue}}</h3>';
        $linkOptionImage->url = "{{{url}}}&size=200";

        $linkOption = new \Dcp\ui\htmlLinkOptions();
        $linkOption->target = "_self";
        $linkOption->title = "Mon test {{value}} {{displayValue}}";
        $linkOption->url = "#action/my:myOptions";

        $options->account()->setLink($linkOptionAccount);
        $options->image()->setLink($linkOptionImage);
        $options->commonOption()->setLink($linkOption);

        return $options;

    }
}

class setLinkTarget_dialog extends AllRenderConfigView
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $linkOptionAccount = new \Dcp\ui\htmlLinkOptions();
        $linkOptionAccount->target = "_dialog";
        $linkOptionAccount->title = "Mon test {{value}} {{displayValue}}";
        $linkOptionAccount->windowHeight = "300px";
        $linkOptionAccount->windowWidth = "500px";
        $linkOptionAccount->windowTitle = "Mon test {{value}} {{displayValue}}";
        $linkOptionAccount->url = "api/v1/documents/{{value}}/views/!defaultEdition.html";

        $linkOptionImage = new \Dcp\ui\htmlLinkOptions();
        $linkOptionImage->target = "_dialog";
        $linkOptionImage->title = ' <h3><img src="{{thumbnail}}&size=100"/>{{displayValue}}</h3>';
        $linkOptionImage->windowHeight = "300px";
        $linkOptionImage->windowWidth = "500px";
        $linkOptionImage->windowTitle = "Mon test {{value}} {{displayValue}}";
        $linkOptionImage->url = "{{{url}}}&size=200";

        $linkOption = new \Dcp\ui\htmlLinkOptions();
        $linkOption->target = "_dialog";
        $linkOption->title = "Mon test {{value}} {{displayValue}}";
        $linkOption->windowHeight = "300px";
        $linkOption->windowWidth = "500px";
        $linkOption->windowTitle = "Mon test {{value}} {{displayValue}}";
        $linkOption->url = "https://fr.wikipedia.org/wiki/{{value}}";

        $options->account()->setLink($linkOptionAccount);
        $options->image()->setLink($linkOptionImage);
        $options->commonOption()->setLink($linkOption);

        return $options;

    }
}

class setLinkTarget_blank extends AllRenderConfigView
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $linkOptionAccount = new \Dcp\ui\htmlLinkOptions();
        $linkOptionAccount->target = "_blank";
        $linkOptionAccount->title = "Mon test {{value}} {{displayValue}}";
        $linkOptionAccount->url = "api/v1/documents/{{value}}/views/!defaultEdition.html";

        $linkOptionImage = new \Dcp\ui\htmlLinkOptions();
        $linkOptionImage->target = "_blank";
        $linkOptionImage->title = ' <h3><img src="{{thumbnail}}&size=100"/>{{displayValue}}</h3>';
        $linkOptionImage->url = "{{{url}}}&size=200";

        $linkOption = new \Dcp\ui\htmlLinkOptions();
        $linkOption->target = "_blank";
        $linkOption->title = "Mon test {{value}} {{displayValue}}";
        $linkOption->url = "https://fr.wikipedia.org/wiki/ {{value}} ";

        $options->account()->setLink($linkOptionAccount);
        $options->image()->setLink($linkOptionImage);
        $options->commonOption()->setLink($linkOption);

        return $options;

    }
}

class setAutoCompleteHtmlLabel extends AllRenderConfigEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setAutoCompleteHtmlLabel("Choisissez un code postal du <b>Pays</b>");

        return $options;

    }
}

class addButtonEditTarget_self extends \Dcp\Ui\DefaultEdit
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonJS.js?ws=" . $version;
        return $js;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonCSS.css?ws=" . $version;
        return $cssReferences;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<i class="fa fa-eye"></i>';
        $viewDoc->url = "#action/my:myOptions";
        $viewDoc->target = "_self";
        $viewDoc->windowWidth = "400px";
        $options->docid()->addButton($viewDoc);

        $cogButton = new \Dcp\Ui\ButtonOptions();
        $cogButton->htmlContent = '<i class="fa fa-cog"></i>';
        $options->text()->addButton($cogButton);

        $superButton = new \Dcp\Ui\ButtonOptions();
        $superButton->htmlContent = '<i class="fa fa-superpowers"></i>';
        $options->commonOption()->addButton($superButton);

        return $options;

    }
}

class addButtonEditTarget_dialog extends \Dcp\Ui\DefaultEdit
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonJS.js?ws=" . $version;
        return $js;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonCSS.css?ws=" . $version;
        return $cssReferences;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<span>  un button autre </span><i class="fa fa-eye"></i>';
        $viewDoc->url = "#action/my:myOptions";
        $viewDoc->target = "_dialog";
        $viewDoc->class = "mybtn mybtn-1";
        $viewDoc->windowWidth = "400px";
        $options->docid()->addButton($viewDoc);

        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<span>  un button autre2 </span><i class="fa fa-eye"></i>';
        $viewDoc->url = "https://fr.wikipedia.org/wiki/ {{value}} ";
        $viewDoc->target = "_dialog";
        $viewDoc->class = "myClass";
        $options->docid()->addButton($viewDoc);

        $cogButton = new \Dcp\Ui\ButtonOptions();
        $cogButton->htmlContent = '<span>  un button grands </span><i class="fa fa-eye"></i>';
        $options->text()->addButton($cogButton);

        $superButton = new \Dcp\Ui\ButtonOptions();
        $superButton->htmlContent = '<span>  un button grands </span><i class="fa fa-eye"></i>';
        $superButton->class = "myClass";
        $options->commonOption()->addButton($superButton);

        return $options;

    }
}

class addButtonConsTarget_self extends \Dcp\Ui\DefaultView
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonJS.js?ws=" . $version;
        return $js;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonCSS.css?ws=" . $version;
        return $cssReferences;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<i class="fa fa-eye"></i>';
        $viewDoc->url = sprintf("api/v1/documents/{{value}}.html");
        $viewDoc->target = "_self";
        $viewDoc->windowWidth = "400px";
        $options->docid()->addButton($viewDoc);

        $cogButton = new \Dcp\Ui\ButtonOptions();
        $cogButton->htmlContent = '<i class="fa fa-cog"></i>';
        $options->text()->addButton($cogButton);

        $superButton = new \Dcp\Ui\ButtonOptions();
        $superButton->htmlContent = '<i class="fa fa-superpowers"></i>';
        $options->commonOption()->addButton($superButton);
        $options->commonOption()->addButton($superButton);


        return $options;

    }
}

class addButtonConsTarget_dialog extends \Dcp\Ui\DefaultView
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonJS.js?ws=" . $version;
        return $js;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonCSS.css?ws=" . $version;
        return $cssReferences;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = ' <p> un bouton qui contient une balise p</p>';
        $viewDoc->url = "https://fr.wikipedia.org/wiki/ {{value}} ";
        $viewDoc->target = "_dialog";
        $viewDoc->class = "mybtn mybtn-1";
        $viewDoc->windowWidth = "400px";
        $options->docid()->addButton($viewDoc);

        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<span>  un button autre2 </span><i class="fa fa-eye"></i>';
        $viewDoc->url = "https://fr.wikipedia.org/wiki/ {{value}} ";
        $viewDoc->target = "_dialog";
        $viewDoc->class = "myClass";
        $options->docid()->addButton($viewDoc);

        $cogButton = new \Dcp\Ui\ButtonOptions();
        $cogButton->htmlContent = '<span>  un button grands </span><i class="fa fa-eye"></i>';
        $options->text()->addButton($cogButton);

        $superButton = new \Dcp\Ui\ButtonOptions();
        $superButton->htmlContent = '<p> un bouton qui contient une balise p</p>';
        $superButton->class = "myClass";
        $options->commonOption()->addButton($superButton);

        return $options;

    }
}


class setAttributeLabel extends \Dcp\Ui\DefaultView
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setAttributeLabel("Mon texte");

        return $options;

    }
}

class eventReady extends \Dcp\Ui\DefaultView
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testEventReadyJS.js?ws=" . $version;
        return $js;
    }

}

;


class eventChange extends \Dcp\Ui\DefaultEdit
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testEventChangeJS.js?ws=" . $version;
        return $js;
    }

}

;

class AttributeModel_getValueEdition extends \Dcp\Ui\DefaultEdit
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/AttributeModel_getValueEdition.js?ws=" . $version;
        return $js;
    }

}

;

class AttributeModel_getValueConsultation extends \Dcp\Ui\DefaultView
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/AttributeModel_getValueConsultation.js?ws=" . $version;
        return $js;
    }

}

;

class AttributeModel_setValueEdition extends \Dcp\Ui\DefaultEdit
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/AttributeModel_setValueEdition.js?ws=" . $version;
        return $js;
    }

}

;

class AttributeModel_setValueConsultation extends \Dcp\Ui\DefaultView
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/AttributeModel_setValueConsultation.js?ws=" . $version;
        return $js;
    }

}

;

class DocumentController_getValueEdition extends \Dcp\Ui\DefaultEdit
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/DocumentController_getValueEdition.js?ws=" . $version;
        return $js;
    }

}

;

class DocumentController_getValueConsultation extends \Dcp\Ui\DefaultView
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/DocumentController_getValueConsultation.js?ws=" . $version;
        return $js;
    }

}

;

class DocumentController_setValueEdition extends \Dcp\Ui\DefaultEdit
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/DocumentController_setValueEdition.js?ws=" . $version;
        return $js;
    }

}

;

class DocumentController_setValueConsultation extends \Dcp\Ui\DefaultView
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/DocumentController_setValueConsultation.js?ws=" . $version;
        return $js;
    }

}

;

class setTranslation extends \Dcp\Ui\DefaultEdit
{
    //vérification lors de la modification de l'attribut
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/setTranslation.js?ws=" . $version;
        return $js;
    }

    public function getOptions(\Doc $document)
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

class setLinkHelp extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}

class setTemplate extends \Dcp\Ui\DefaultEdit
{

    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/setTemplate.css?ws=" . $version;
        return $cssReferences;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/setTemplate.js?ws=" . $version;
        return $js;
    }


    public function getOptions(\Doc $document)
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

class DocumentController_reinitDocument             extends \Dcp\Ui\DefaultEdit
{

    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/userButton.css?ws=" . $version;
        return $cssReferences;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/DocumentController_reinitDocument.js?ws=" . $version;
        return $js;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options = parent::getOptions($document);
        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<span>  Reinitialiser le document </span><i class="fa fa-eye"></i>';
        $viewDoc->class = "mybtn_DocumentController_reinitDocument userButton";
        $viewDoc->windowWidth = "400px";
        $options->commonOption( myAttributes::test_ddui_all__title)->addButton($viewDoc);


        return $options;

    }
}
class DocumentController_fetchDocument              extends \Dcp\Ui\DefaultEdit
{

    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/userButton.css?ws=" . $version;
        return $cssReferences;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/DocumentController_fetchDocument.js?ws=" . $version;
        return $js;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options = parent::getOptions($document);
        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<span>  Fetcher le document </span><i class="fa fa-eye"></i>';
        $viewDoc->class = "mybtn_DocumentController_fetchDocument userButton";
        $viewDoc->windowWidth = "400px";
        $options->commonOption( myAttributes::test_ddui_all__title)->addButton($viewDoc);


        return $options;

    }
}
class DocumentController_saveDocument               extends \Dcp\Ui\DefaultEdit
{

    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstAddButtonCSS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/userButton.css?ws=" . $version;
        return $cssReferences;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/DocumentController_saveDocument.js?ws=" . $version;
        return $js;
    }



    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $viewDoc = new \Dcp\Ui\ButtonOptions();
        $viewDoc->htmlContent = '<span>  Sauvegarder le document </span><i class="fa fa-eye"></i>';
        $viewDoc->class = "mybtn_DocumentController_saveDocument userButton";
        $viewDoc->windowWidth = "400px";
        $options->commonOption( myAttributes::test_ddui_all__title)->addButton($viewDoc);


        return $options;

    }
}
class DocumentController_changeStateDocumentclass   extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}
class DocumentCon_roller_deleteDocument             extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}
class DocumentController_restoreDocument            extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}
class DocumentController_getProperty                extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}
class DocumentController_getProperties              extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}
class DocumentController_hasAttribute               extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}
class DocumentController_getAttribute               extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}
class DocumentController_getAttributes              extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}
class DocumentController_hasMenu                    extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}
class DocumentController_getMenu                    extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}
class DocumentController_getMenus                   extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        return $options;

    }
}








