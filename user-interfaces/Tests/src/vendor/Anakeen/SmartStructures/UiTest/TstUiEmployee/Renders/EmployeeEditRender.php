<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use Anakeen\Ui\CommonRenderOptions;
use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Tst_ddui_employee as myAttribute;

class EmployeeEditRender extends \Anakeen\Ui\DefaultEdit
{

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Employee edit";
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->document()->setTabPlacement(\Anakeen\Ui\DocumentRenderOptions::tabTopProportionalPlacement);

        $displayPosition
            = '<b style="float:right;color:orange;border:solid 1px orange;margin-left:2px">{{renderOptions.description.position}}</b>';
        $options->tab(myAttribute::tst_t_infos_administratives)->setDescription(
            "<p>Section contenant les informations nécessaires pour le paiement des notes de frais.</p>
            <p><b>Seul le service comptabilité à accés à ces informations.</b></p>",
            \Anakeen\Ui\CommonRenderOptions::clickPosition
        );

        $options->tab(myAttribute::tst_t_infos_administratives)->setDescription(
            "<p>Section contenant les informations nécessaires pour le paiement des notes de frais.</p>
            <p><b>Seul le service comptabilité à accés à ces informations.</b></p>",
            \Anakeen\Ui\CommonRenderOptions::topPosition
        );


        $options->frame(myAttribute::tst_f_adresseperso)->setDescription(
            "<p>Infos personnelles</p>
            <p><b>Seul le service comptabilité à accés à ces informations.</b></p>",
            \Anakeen\Ui\CommonRenderOptions::bottomLabelPosition
        );


        $options->frame(myAttribute::tst_administratif)->setDescription(
            "<p>Section contenant les informations nécessaires pour le paiement des notes de frais.</p>
            <p><b>Seul le service comptabilité à accés à ces informations.</b></p>",
            \Anakeen\Ui\CommonRenderOptions::bottomLabelPosition
        );


        $options->frame(myAttribute::tst_f_statut)
            ->setDescription(
                "<p>Informations relatives à la carrière professionnelle</p>",
                \Anakeen\Ui\CommonRenderOptions::topPosition
            );
        $options->text(myAttribute::tst_adp_localite)->setAttributeLabel("Téléphone");
        $options->text(myAttribute::tst_adp_localite)
            ->setDescription(
                "<p>Numéro d'astreinte<p><p> Format international : <b style2=\"white-space:nowrap\">+33 C CC CC CC CC</b></p>",
                \Anakeen\Ui\CommonRenderOptions::clickPosition
            );

        $options->arrayAttribute(myAttribute::tst_adp_phone_array)
            ->setDescription(
                "$displayPosition <p>Liste des numéros de téléphone.</p><p><i>Ces numéros ne sont accessibles que par votre secrétariat afin vous joindre en fonction de vos déplacements.</i></p>",
                \Anakeen\Ui\CommonRenderOptions::bottomLabelPosition
            );

        $options->text(myAttribute::tst_adp_phone_type)
            ->setDescription(
                "<p>Différent n° de téléphone</p><p><i>Professionnel et personnel</i></p>",
                \Anakeen\Ui\CommonRenderOptions::bottomLabelPosition
            );
        $options->text(myAttribute::tst_adp_phone_num)
            ->setDescription(
                "<p>Numéro de téléphone<p><p> Format international : <b style=\"white-space:nowrap\">+33 C CC CC CC CC</b></p>",
                \Anakeen\Ui\CommonRenderOptions::bottomLabelPosition
            );


        $options->text(myAttribute::tst_ban_agence)->setDescription($displayPosition
            . "<p>Nom et localisation de l'agence bancaire</p>", \Anakeen\Ui\CommonRenderOptions::topPosition);

        $options->text(myAttribute::tst_ban_etablissement)->setDescription(
            $displayPosition
            . "<p>L'identifiant domestique du compte : code banque<p><b> (5 chiffres)</b>",
            \Anakeen\Ui\CommonRenderOptions::topValuePosition
        );

        $options->text(myAttribute::tst_ban_guichet)->setDescription($displayPosition
            . "<p>Le code guichet de la banque<p><b> (5 chiffres)</b>", \Anakeen\Ui\CommonRenderOptions::topLabelPosition);

        $options->text(myAttribute::tst_ban_numcompte)->setDescription(
            $displayPosition
            . "<p>Numéro du compte bancaire<p><b> (11 chiffres ou lettres)</b>",
            \Anakeen\Ui\CommonRenderOptions::bottomValuePosition
        );

        $options->text(myAttribute::tst_ban_clecompte)->setDescription(
            $displayPosition
            . "<p>Clé du relevé d'identité bancaire<p><b> (2 chiffres)</b>",
            \Anakeen\Ui\CommonRenderOptions::topPosition
        );

        $options->text(myAttribute::tst_ban_iban)->setDescription(
            $displayPosition
            . "<p>Le code IBAN <i>(International Bank Account Number)</i> représenté par une série de <b>27</b> chiffres et de lettres</p>",
            \Anakeen\Ui\CommonRenderOptions::topPosition,
            "<p>Reprenant notamment (mais regroupés différemment) le code banque, le code guichet et le numéro de compte</p>",
            true
        );

        $options->text(myAttribute::tst_ban_bic)->setDescription(
            $displayPosition
            . "<p>Le code BIC <i>(Business Identifier Code)</i> représenté par une série de <b>11 ou 8</b> lettres .</p>",
            \Anakeen\Ui\CommonRenderOptions::bottomPosition
        );

        $options->frame(myAttribute::tst_f_adresseperso)->setDescription(
            $displayPosition
            . "<p>Compléter ici vos informations personnelles</p>",
            \Anakeen\Ui\CommonRenderOptions::clickPosition
        );


        $options->text(myAttribute::tst_dest_nom)->setDescription(
            $displayPosition
            . "<p>Titulaire du compte</p>",
            \Anakeen\Ui\CommonRenderOptions::bottomLabelPosition
        );
        return $options;
    }
}
