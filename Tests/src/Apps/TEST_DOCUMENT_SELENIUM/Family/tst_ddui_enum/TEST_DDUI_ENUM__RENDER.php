<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Test\Ddui;

use Dcp\AttributeIdentifiers\TST_DDUI_ENUM as myAttributes;

class EnumRenderConfigEditDefault extends \Dcp\Ui\DefaultEdit
{
    public function getLabel(\Doc $document = null)
    {
        return "Enum Edit Default";
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->document()->setTabPlacement(
            \Dcp\Ui\DocumentRenderOptions::tabTopProportionalPlacement
        );
        return $options;
    }


    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        $css = parent::getCssReferences($document);
        $css["tstotherenum"] = "TEST_DOCUMENT_SELENIUM/Layout/testOtherEnum.css?ws=".$version;
        return $css;
    }
}

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


class EnumRenderConfigEditButtons extends EnumRenderConfigEditDefault
{
    public function getLabel(\Doc $document = null)
    {
        return "Enum Edit Buttons";
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
        $options->arrayAttribute()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        
        $options->enum(myAttributes::test_ddui_enum__enumcountry)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->enum(myAttributes::test_ddui_enum__enumtext)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->enum(myAttributes::test_ddui_enum__enumnumber)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->enum(myAttributes::test_ddui_enum__enumbool)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        
        $options->frame(myAttributes::test_ddui_enum__fr_enumsimple)->setTemplate(<<< 'HTML'
                <div class="container-fluid"><div class="row">
            <div class="col-md-8">{{{attributes.test_ddui_enum__enumcountry.htmlView}}}</div>
            <div class="col-md-4">
               {{{attributes.test_ddui_enum__enumtext.htmlView}}}
               {{{attributes.test_ddui_enum__enumnumber.htmlView}}}
               {{{attributes.test_ddui_enum__enumbool.htmlView}}}
            </div></div></div>
HTML
) ;
        $options->enum(myAttributes::test_ddui_enum__enumscountry)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->enum(myAttributes::test_ddui_enum__enumstext)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->enum(myAttributes::test_ddui_enum__enumsnumber)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->frame(myAttributes::test_ddui_enum__fr_enummultiple)->setTemplate(<<< 'HTML'
                  <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">{{{attributes.test_ddui_enum__enumscountry.htmlView}}}</div>

                </div>
                <div class="row">
                    <div class="col-md-6">{{{attributes.test_ddui_enum__enumstext.htmlView}}}</div>
                    <div class="col-md-6">{{{attributes.test_ddui_enum__enumsnumber.htmlView}}}</div>
                </div>
            </div>
HTML
) ;
        
        $options->enum(myAttributes::test_ddui_enum__srvcountry)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->enum(myAttributes::test_ddui_enum__srvtext)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->enum(myAttributes::test_ddui_enum__srvnumber)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->enum(myAttributes::test_ddui_enum__srvbool)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->frame(myAttributes::test_ddui_enum__fr_srvsimple)->setTemplate(<<< 'HTML'
                <div class="container-fluid"><div class="row">
            <div class="col-md-8">{{{attributes.test_ddui_enum__srvcountry.htmlView}}}</div>
            <div class="col-md-4">
               {{{attributes.test_ddui_enum__srvtext.htmlView}}}
               {{{attributes.test_ddui_enum__srvnumber.htmlView}}}
               {{{attributes.test_ddui_enum__srvbool.htmlView}}}
            </div></div></div>
HTML
) ;
        $options->enum(myAttributes::test_ddui_enum__srvscountry)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->enum(myAttributes::test_ddui_enum__srvstext)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->enum(myAttributes::test_ddui_enum__srvsnumber)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->frame(myAttributes::test_ddui_enum__fr_srvmultiple)->setTemplate(<<< 'HTML'
                  <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">{{{attributes.test_ddui_enum__srvscountry.htmlView}}}</div>

                </div>
                <div class="row">
                    <div class="col-md-6">{{{attributes.test_ddui_enum__srvstext.htmlView}}}</div>
                    <div class="col-md-6">{{{attributes.test_ddui_enum__srvsnumber.htmlView}}}</div>
                </div>
            </div>
HTML
) ;
        
        $options->arrayAttribute(myAttributes::test_ddui_enum__array_singleenum)->setTemplate(<<< 'HTML'
                    <table class="dcpArray__table">
                        <thead>
                            <tr>
                                {{#attribute.toolsEnabled}}<th></th>{{/attribute.toolsEnabled}}
                                <th class="customMiddle">
                                    {{attributes.test_ddui_enum__enumtown_array.label}}
                                </th>
                                <th>
                                    {{attributes.test_ddui_enum__enumtext_array.label}}
                                    {{attributes.test_ddui_enum__enumnumber_array.label}} et
                                    {{attributes.test_ddui_enum__enumbool_array.label}}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        {{#attribute.rows}}
                            <tr>
                                {{#attribute.toolsEnabled}}<td>{{{rowTools}}}</td>{{/attribute.toolsEnabled}}
                                <td>

                                    {{{content.test_ddui_enum__enumtown_array.htmlContent}}}
                                </td>
                                <td>
                                    {{{content.test_ddui_enum__enumtext_array.htmlContent}}}
                                    <br/>
                                    {{{content.test_ddui_enum__enumnumber_array.htmlContent}}}
                                    <br/>
                                    {{{content.test_ddui_enum__enumbool_array.htmlContent}}}
                                </td>
                            </tr>
                        {{/attribute.rows}}
                        </tbody>
                    </table>
                    <div>
                        {{{attribute.tableTools}}}
                    </div>

HTML
) ;
        
        $options->arrayAttribute(myAttributes::test_ddui_enum__array_multipleenum)->setTemplate(<<< 'HTML'
                    <table class="dcpArray__table">
                        <thead>
                            <tr>
                                {{#attribute.toolsEnabled}}<th></th>{{/attribute.toolsEnabled}}
                                <th class="customMiddle">
                                    {{attributes.test_ddui_enum__enumstown_array.label}}
                                </th>
                                <th>
                                    {{attributes.test_ddui_enum__enumstext_array.label}} et
                                    {{attributes.test_ddui_enum__enumsnumber_array.label}}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        {{#attribute.rows}}
                            <tr>
                                {{#attribute.toolsEnabled}}<td>{{{rowTools}}}</td>{{/attribute.toolsEnabled}}
                                <td>

                                    {{{content.test_ddui_enum__enumstown_array.htmlContent}}}
                                </td>
                                <td>
                                    {{{content.test_ddui_enum__enumstext_array.htmlContent}}}
                                    <br/>
                                    {{{content.test_ddui_enum__enumsnumber_array.htmlContent}}}

                                </td>
                            </tr>
                        {{/attribute.rows}}
                        </tbody>
                    </table>
                    <div>
                        {{{attribute.tableTools}}}
                    </div>

HTML
        ) ;
        return $options;
    }
}

class EnumRenderConfigEditHorizontal extends EnumRenderConfigEditButtons
{
    public function getLabel(\Doc $document = null)
    {
        return "Enum Edit Horizontal";
    }
    
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        
        $options->enum()->setDisplay(\Dcp\Ui\EnumRenderOptions::horizontalDisplay);
        //$options->enum()->useOtherChoice(true);
        return $options;
    }
    
    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["tstenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/tstenumhorizontal.css";
        return $css;
    }
}

class EnumRenderConfigEditVertical extends EnumRenderConfigEditButtons
{
    public function getLabel(\Doc $document = null)
    {
        return "Enum Edit Vertical";
    }
    
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        
        $options->enum()->setDisplay(\Dcp\Ui\EnumRenderOptions::verticalDisplay);
        //$options->enum()->useOtherChoice(true);
        return $options;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["tstenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/tstenumvertical.css";
        return $css;
    }
}

class EnumRenderConfigEditOther extends EnumRenderConfigEdit
{
    public function getLabel(\Doc $document = null)
    {
        return "Enum Edit Other";
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->enum()->useOtherChoice(true);

        $tagTemplate='<span class="dcpAttribute__content--enum-single' .
            '#if (exists === false) { #'.
            ' dcpAttribute__content--enum-single--other'.
            '#}#'.
            '"> #: displayValue #</span>';
        $options->enum()->setOption("kendoMultiSelectConfiguration", array(
            "tagTemplate"=>$tagTemplate));
        $options->enum()->setOption("kendoDropDownConfiguration", array(
            "valueTemplate"=>$tagTemplate));

        return $options;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        $css = parent::getCssReferences($document);
        $css["tsteditverticalenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/tstenumvertical.css?ws=".$version;
        return $css;
    }
}

class EnumRenderConfigView extends \Dcp\Ui\DefaultView
{
    
    public function getLabel(\Doc $document = null)
    {
        return "Enum View";
    }

    public function getOptions(\Doc $document)
    {
        $options= parent::getOptions(
            $document
        );

        $options->document()->setTabPlacement(\Dcp\Ui\DocumentRenderOptions::tabTopProportionalPlacement);
        return $options;
    }
    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        $css = parent::getCssReferences($document);
        $css["tstviewenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/testViewEnum.css?ws=".$version;
        $css["tstotherenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/testOtherEnum.css?ws=".$version;
        return $css;
    }
    public function getJsReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        $jsReferences = parent::getJsReferences($document);
        $jsReferences["tstviewenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/testViewEnum.js?ws=".$version;
        return $jsReferences;
    }
}
