<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Tst_ddui_employee as myAttribute;

class EmployeeViewRender extends \Anakeen\Ui\DefaultView
{

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Employee view";
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return \Dcp\ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::autoPosition);

        $originalLabel = '<p><b>{{label}}</b></p>';

        $options->text(myAttribute::tst_ban_agence)->setDescription($originalLabel
            . "<p>Nom et localisation de l'agence bancaire</p>", \Anakeen\Ui\CommonRenderOptions::topPosition)
            ->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_etablissement)->setDescription($originalLabel
            . "<p>L'identifiant domestique du compte : code banque <b> (5 chiffres)</b><p>",
            \Anakeen\Ui\CommonRenderOptions::topPosition)->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_guichet)->setDescription($originalLabel
            . "<p>Le code guichet de la banque<b> (5 chiffres)</b><p>", \Anakeen\Ui\CommonRenderOptions::topPosition)
            ->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_numcompte)->setDescription($originalLabel
            . "<p>Numéro du compte bancaire<b> (11 chiffres ou lettres)</b><p>",
            \Anakeen\Ui\CommonRenderOptions::topPosition)->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_clecompte)->setDescription($originalLabel
            . "<p>Clé du relevé d'identité bancaire <b> (2 chiffres)</b></p>", \Anakeen\Ui\CommonRenderOptions::topPosition)
            ->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_iban)->setDescription($originalLabel
            . "<p>Le code IBAN <i>(International Bank Account Number)</i> représenté par une série de <b>27</b> chiffres et de lettres</p>",
            \Anakeen\Ui\CommonRenderOptions::topPosition,
            "<p>Reprenant notamment (mais regroupés différemment) le code banque, le code guichet et le numéro de compte</p>",
            true)->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);

        $options->text(myAttribute::tst_ban_bic)->setDescription($originalLabel
            . "<p>Le code BIC <i>(Business Identifier Code)</i> représenté par une série de <b>11 ou 8</b> lettres .</p>",
            \Anakeen\Ui\CommonRenderOptions::topPosition)->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);
        return $options;
    }

    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["tstCustomEmployee"] = "/TEST_DOCUMENT_SELENIUM/Layout/customEmployee.css";
        return $css;
    }
}
