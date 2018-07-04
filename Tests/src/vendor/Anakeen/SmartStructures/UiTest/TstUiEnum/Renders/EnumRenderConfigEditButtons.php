<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEnum\Renders;

use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_enum as myAttributes;

class EnumRenderConfigEditButtons extends EnumRenderConfigEditDefault
{
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Enum Edit Buttons";
    }
    
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
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
