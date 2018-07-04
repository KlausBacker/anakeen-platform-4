<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use Dcp\Ui\RenderOptions;
use \SmartStructure\Fields\Tst_ddui_employee as myAttribute;

class EmployeeEditRender extends \Dcp\Ui\DefaultEdit
{

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Employee edit";
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return \Dcp\ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
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
