<?php

namespace Anakeen\SmartStructures\UiTest\TstUiShowEmpty\Renders;

use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Tst_se_showempty as myAttribute;

class TstUiShowEmptyEditRender extends \Anakeen\Ui\DefaultEdit
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

        $options->text(myAttribute::tst_showempty__frame_title_text)->showEmptyContent("Ce champs texte est actuellement vide");

        $options->text(myAttribute::tst_showempty__frame_empty)->showEmptyContent("Ce cadre est vide");

        $options->text(myAttribute::tst_showempty__frame_write)->showEmptyContent("Cadre vide car le champs texte présent à l'intérieur n'est visible qu'en lecture");
        $options->text(myAttribute::tst_showempty__frame_write_field_read)->showEmptyContent("Ce champs texte est actuellement vide");
        
        $options->text(myAttribute::tst_showempty__tab_aaa)->showEmptyContent("L'onglet N°1 est vide");

        $options->text(myAttribute::tst_showempty__tab_bbb)->setDescription("Le cadre n'est pas visible car il n'y a pas de `showEmptyContent()`")->showEmptyContent("L'onglet N°2 est vide");
        $options->text(myAttribute::tst_showempty__tab_bbb_frame);

        $options->text(myAttribute::tst_showempty__tab_ccc)->showEmptyContent("L'onglet N°3 est vide");
        $options->text(myAttribute::tst_showempty__tab_ccc_frame)->showEmptyContent("Le cadre de cet onglet est vide");

        $options->text(myAttribute::tst_showempty__tab_write)->setDescription("Le cadre n'est visible qu'en lecture")->showEmptyContent("L'onglet N°4 est vide");
        $options->text(myAttribute::tst_showempty__tab_write_frame_read)->showEmptyContent("Le cadre de cet onglet est vide");

        return $options;
    }
}
