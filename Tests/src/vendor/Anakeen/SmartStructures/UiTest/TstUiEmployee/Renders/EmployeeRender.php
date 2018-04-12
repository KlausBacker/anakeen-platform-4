<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use \SmartStructure\Attributes\Tst_ddui_employee as myAttribute;

class EmployeeEditRender extends \Dcp\Ui\DefaultEdit
{

    public function getLabel(\Doc $document = null)
    {
        return "Employee edit";
    }

    /**
     * @param \Doc $document Document instance
     *
     * @return \Dcp\ui\RenderOptions
     */
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->document()->setTabPlacement(\Dcp\Ui\DocumentRenderOptions::tabTopProportionalPlacement);


        //$options->frame()->setCollapse(true);
        //$options->frame(myAttribute::tst_f_dombancaire)->setCollapse(false);
        $options->tab(myAttribute::tst_t_infos_administratives)->setDescription("<p>Section contenant les informations nécessaires pour le paiement des notes de frais.</p>
            <p><b>Seul le service comptabilité à accés à ces informations.</b></p>",
            \Dcp\Ui\CommonRenderOptions::clickPosition);

        $options->frame(myAttribute::tst_f_statut)
            ->setDescription("<p>Informations relatives à la carrière professionnelle</p>",
                \Dcp\Ui\CommonRenderOptions::topPosition);
        $options->text(myAttribute::tst_adp_localite)->setAttributeLabel("Téléphone");
        $options->text(myAttribute::tst_adp_localite)
            ->setDescription("<p>Numéro d'astreinte<p><p> Format international : <b style2=\"white-space:nowrap\">+33 C CC CC CC CC</b></p>",
                \Dcp\Ui\CommonRenderOptions::clickPosition);

        $options->arrayAttribute(myAttribute::tst_adp_phone_array)
            ->setDescription("<p>Liste des numéros de téléphone.</p><p><i>Ces numéros ne sont accessibles que par votre secrétariat afin vous joindre en fonction de vos déplacements.</i></p>",
                \Dcp\Ui\CommonRenderOptions::topPosition);

        $options->text(myAttribute::tst_adp_phone_type)
            ->setDescription("<p>Différent n° de téléphone</p><p><i>Professionnel et personnel</i></p>",
                \Dcp\Ui\CommonRenderOptions::bottomLabelPosition);
        $options->text(myAttribute::tst_adp_phone_num)
            ->setDescription("<p>Numéro de téléphone<p><p> Format international : <b style=\"white-space:nowrap\">+33 C CC CC CC CC</b></p>",
                \Dcp\Ui\CommonRenderOptions::bottomLabelPosition);

        $displayPosition
            = '<b style="float:right;color:orange;border:solid 1px orange;margin-left:2px">{{renderOptions.description.position}}</b>';
        $options->text(myAttribute::tst_ban_agence)->setDescription($displayPosition
            . "<p>Nom et localisation de l'agence bancaire</p>", \Dcp\Ui\CommonRenderOptions::leftPosition);

        $options->text(myAttribute::tst_ban_etablissement)->setDescription($displayPosition
            . "<p>L'identifiant domestique du compte : code banque<p><b> (5 chiffres)</b>",
            \Dcp\Ui\CommonRenderOptions::topValuePosition);

        $options->text(myAttribute::tst_ban_guichet)->setDescription($displayPosition
            . "<p>Le code guichet de la banque<p><b> (5 chiffres)</b>", \Dcp\Ui\CommonRenderOptions::topLabelPosition);

        $options->text(myAttribute::tst_ban_numcompte)->setDescription($displayPosition
            . "<p>Numéro du compte bancaire<p><b> (11 chiffres ou lettres)</b>",
            \Dcp\Ui\CommonRenderOptions::bottomValuePosition);

        $options->text(myAttribute::tst_ban_clecompte)->setDescription($displayPosition
            . "<p>Clé du relevé d'identité bancaire<p><b> (2 chiffres)</b>",
            \Dcp\Ui\CommonRenderOptions::rightPosition);

        $options->text(myAttribute::tst_ban_iban)->setDescription($displayPosition
            . "<p>Le code IBAN <i>(International Bank Account Number)</i> représenté par une série de <b>27</b> chiffres et de lettres</p>",
            \Dcp\Ui\CommonRenderOptions::topPosition,
            "<p>Reprenant notamment (mais regroupés différemment) le code banque, le code guichet et le numéro de compte</p>",
            true);

        $options->text(myAttribute::tst_ban_bic)->setDescription($displayPosition
            . "<p>Le code BIC <i>(Business Identifier Code)</i> représenté par une série de <b>11 ou 8</b> lettres .</p>",
            \Dcp\Ui\CommonRenderOptions::bottomPosition);

        $options->frame(myAttribute::tst_f_adresseperso)->setDescription($displayPosition
            . "<p>Compléter ici vos informations personnelles</p>",
            \Dcp\Ui\CommonRenderOptions::clickPosition);
        return $options;
    }
}

class EmployeeViewRender extends \Dcp\Ui\DefaultView
{

    public function getLabel(\Doc $document = null)
    {
        return "Employee view";
    }

    /**
     * @param \Doc $document Document instance
     *
     * @return \Dcp\ui\RenderOptions
     */
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::autoPosition);

        $originalLabel = '<p><b>{{label}}</b></p>';

        $options->text(myAttribute::tst_ban_agence)->setDescription($originalLabel
            . "<p>Nom et localisation de l'agence bancaire</p>", \Dcp\Ui\CommonRenderOptions::topPosition)
            ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_etablissement)->setDescription($originalLabel
            . "<p>L'identifiant domestique du compte : code banque <b> (5 chiffres)</b><p>",
            \Dcp\Ui\CommonRenderOptions::topPosition)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_guichet)->setDescription($originalLabel
            . "<p>Le code guichet de la banque<b> (5 chiffres)</b><p>", \Dcp\Ui\CommonRenderOptions::topPosition)
            ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_numcompte)->setDescription($originalLabel
            . "<p>Numéro du compte bancaire<b> (11 chiffres ou lettres)</b><p>",
            \Dcp\Ui\CommonRenderOptions::topPosition)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_clecompte)->setDescription($originalLabel
            . "<p>Clé du relevé d'identité bancaire <b> (2 chiffres)</b></p>", \Dcp\Ui\CommonRenderOptions::topPosition)
            ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_iban)->setDescription($originalLabel
            . "<p>Le code IBAN <i>(International Bank Account Number)</i> représenté par une série de <b>27</b> chiffres et de lettres</p>",
            \Dcp\Ui\CommonRenderOptions::topPosition,
            "<p>Reprenant notamment (mais regroupés différemment) le code banque, le code guichet et le numéro de compte</p>",
            true)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_bic)->setDescription($originalLabel
            . "<p>Le code BIC <i>(Business Identifier Code)</i> représenté par une série de <b>11 ou 8</b> lettres .</p>",
            \Dcp\Ui\CommonRenderOptions::topPosition)->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        return $options;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["tstCustomEmployee"] = "TEST_DOCUMENT_SELENIUM/Layout/customEmployee.css";
        return $css;
    }
}

class EmployeeCustomRender extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Doc $document)
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

class EmployeeFrameViewSRCLRRender extends \Dcp\Ui\DefaultView
{
    public static function setColumn(\Dcp\Ui\RenderOptions &$options, $direction = \Dcp\Ui\FrameRenderOptions::leftRightDirection)
    {

        $options->frame()->setResponsiveColumns([["number" => 2, "minWidth" => "70rem", "grow" => true, "direction" => $direction]]);


        $options->frame(myAttribute::tst_f_identite)->setResponsiveColumns([
            ["number" => 2, "minWidth" => "70rem", "maxWidth" => "100rem", "direction" => $direction],
            ["number" => 3, "minWidth" => "100rem", "maxWidth" => "110rem", "direction" => $direction],
            ["number" => 4, "minWidth" => "110rem", "maxWidth" => "120rem", "direction" => $direction],
            ["number" => 5, "minWidth" => "120rem", "maxWidth" => "130rem", "direction" => $direction, "grow" => false],
            ["number" => 6, "minWidth" => "130rem", "direction" => $direction, "grow" => false]
        ]);

        $options->frame(myAttribute::tst_f_adresseperso)->setResponsiveColumns([
            ["number" => 2, "minWidth" => "600px", "maxWidth" => "800px", "direction" => $direction],
            ["number" => 3, "direction" => $direction],
        ]);
        $options->frame(myAttribute::tst_f_dombancaire)->setResponsiveColumns([
            ["number" => 2, "minWidth" => "500px", "maxWidth" => "700px", "direction" => $direction],
            ["number" => 3, "maxWidth" => "800px", "direction" => $direction],
            ["number" => 4, "maxWidth" => "1900px", "direction" => $direction],
            ["number" => 6, "maxWidth" => "2200px", "direction" => $direction],
            ["number" => 12]
        ]);

    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->showEmptyContent("Pas d'information");
        self::setColumn($options);


        return $options;
    }
}

class EmployeeFrameEditSRCLRRender extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        EmployeeFrameViewSRCLRRender::setColumn($options);

        return $options;
    }
}


class EmployeeFrameViewSRCTBRender extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        EmployeeFrameViewSRCLRRender::setColumn($options, \Dcp\Ui\FrameRenderOptions::topBottomDirection);

        return $options;
    }
}

class EmployeeFrameEditSRCTBRender extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        EmployeeFrameViewSRCLRRender::setColumn($options, \Dcp\Ui\FrameRenderOptions::topBottomDirection);

        return $options;
    }
}


class EmployeeTabViewRender extends \Dcp\Ui\DefaultView
{
    public static function setColumn(\Dcp\Ui\RenderOptions &$options)
    {

        $options->tab()->setResponsiveColumns([
            ["number" => 2, "minWidth" => "100rem", "grow" => true]

        ]);

        $options->tab(myAttribute::tst_t_infos_administratives)->setResponsiveColumns([
            ["number" => 2, "minWidth" => "70rem", "maxWidth" => "100rem"],
            ["number" => 3, "maxWidth" => "130rem"],
            ["number" => 4]
        ]);

        $options->frame()->setResponsiveColumns([["number" => 2, "minWidth" => "400px", "grow" => true]]);
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->showEmptyContent("Pas d'information");

        self::setColumn($options);


        return $options;
    }
}

class EmployeeTabEditRender extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        EmployeeTabViewRender::setColumn($options);


        return $options;
    }
}