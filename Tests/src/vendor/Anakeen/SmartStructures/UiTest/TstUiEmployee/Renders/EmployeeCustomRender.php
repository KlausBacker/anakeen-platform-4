<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use \SmartStructure\Fields\Tst_ddui_employee as myAttribute;

class EmployeeCustomRender extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);


        $options->commonOption()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::autoPosition);
        $tplIdent
            = <<< 'HTML'
        <div class="Bof" style="outline:dashed 1px red; margin: 1rem">
           
           {{{attributes.tst_prenom.htmlView}}} {{{attributes.tst_nom.htmlView}}}
          </div>
HTML;

        $options->frame(myAttribute::tst_t_identite)->setTemplate($tplIdent);
        $tplIdent
            = <<< 'HTML'
        <div class="firstname" style="outline:dotted 2px green; font-size:130%">
           <p>The first name</p>
           <div>{{{attribute.htmlDefaultContent}}} </div>
          </div>
HTML;
        $options->text(myAttribute::tst_prenom)->setTemplate($tplIdent);

        $tplIdent
            = <<< 'HTML'
        <div class="firstname" style="outline:dotted 2px blue; font-size:130%">
           <p>The last name</p>
           <div>{{{attribute.htmlDefaultView}}} </div>
          </div>
HTML;
        $options->text(myAttribute::tst_nom)->setTemplate($tplIdent)
            ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);

        $tplIdent
            = <<< 'HTML'
        <div class="firstname" style="outline:dotted 5px orange;margin: 1rem">
           <h2>{{attribute.label}}</h2>
           <div>{{{attributes.tst_a_emp_tel.htmlDefaultContent}}} </div>
          </div>
HTML;
        $options->arrayAttribute(myAttribute::tst_a_emp_tel)->setTemplate($tplIdent);


        $tplIdent
            = <<< 'HTML'
        <div class="firstname" style="outline:dotted 5px purple;margin:1rem">
           <table class="dcpArray__table">
    <thead>
        <tr>
            {{#attribute.toolsEnabled}}<th>Outils</th>{{/attribute.toolsEnabled}}
            <th class="special">
                Langue
            </th>
            <th>
                Niveau de compréhension
            </th>
        </tr>
    </thead>
    <tbody>
    {{#attribute.rows}}
        <tr>
            {{#attribute.toolsEnabled}}<td>{{{rowTools}}}</td>{{/attribute.toolsEnabled}}
            <td>
                
                <br/>
                {{{content.tst_lang.htmlContent}}}
            </td>
            <td>
                <table style="width:100%">
                    <tr><td style="width:10rem">{{content.tst_lang_lu.label}} :</td><td>{{{content.tst_lang_lu.htmlContent}}}</td></tr>
                    <tr><td>{{content.tst_lang_ecrit.label}} :</td><td>{{{content.tst_lang_ecrit.htmlContent}}}</td></tr>
                    <tr><td>{{content.tst_lang_parle.label}} :</td><td>{{{content.tst_lang_parle.htmlContent}}}</td></tr>
                </table>    
                 
            </td>
        </tr>
    {{/attribute.rows}}
    </tbody>
</table>
<div>
    {{{attribute.tableTools}}}
</div>
          </div>
HTML;
        $options->arrayAttribute(myAttribute::tst_a_langues)->setTemplate($tplIdent);


        return $options;
    }
}
