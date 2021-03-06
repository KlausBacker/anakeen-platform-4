<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\UiTest\TstUiDocid\Renders;

use Anakeen\Core\ContextManager;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_docid as myAttributes;

class DocidRenderConfigView extends \Anakeen\Ui\DefaultView
{
    
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Docid View";
    }
    
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        /**
         * @var $link \Anakeen\Ui\HtmlLinkOptions
         */
        $link = $options->docid(myAttributes::test_ddui_docid__single2)->getOption(\Anakeen\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir l'historique de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__single2)->setFormat('<i class="fa fa-history"/> {{displayValue}} (H)');
        
        $link = $options->docid(myAttributes::test_ddui_docid__single3)->getOption(\Anakeen\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir les propriétés de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__single3)->setFormat("<i class=\"fa fa-info-circle\"/> {{displayValue}} (P)");
        
        $link = $options->docid(myAttributes::test_ddui_docid__multiple2)->getOption(\Anakeen\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir l'historique de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__multiple2)->setFormat("<i class=\"fa fa-history\"/> {{displayValue}} (H)");
        
        $link = $options->docid(myAttributes::test_ddui_docid__multiple3)->getOption(\Anakeen\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir les propriétés de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__multiple3)->setFormat("<i class=\"fa fa-info-circle\"/> {{displayValue}} (P)");
        
        $link = $options->docid(myAttributes::test_ddui_docid__single_link)->getOption(\Anakeen\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir les propriétés de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__single_link)->setFormat("<i class=\"fa fa-info-circle\"/> {{displayValue}} (P)");
        
        $link = $options->docid(myAttributes::test_ddui_docid__multiple_link)->getOption(\Anakeen\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir les propriétés de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__multiple_link)->setFormat("<i class=\"fa fa-info-circle\"/> {{displayValue}} (P)");

        //$link = $options->text(myAttributes::test_ddui_docid__histo1)->getOption(\Anakeen\Ui\CommonRenderOptions::htmlLinkOption);
        $linkTitle = sprintf("Voir l'historique <br/><b>\"%s\"<b>", $document->getHtmlTitle($document->getRawValue(myAttributes::test_ddui_docid__single1)));
        $options->text(myAttributes::test_ddui_docid__histo1)->setFormat($linkTitle);
        //$options->text(myAttributes::test_ddui_docid__link_histo)->showEmptyContent("Voir l'historique");

        $options->text(myAttributes::test_ddui_docid__link_histo)->setFormat("Voir historique lien du lien particulier");

        $link = $options->docid()->getOption(\Anakeen\Ui\CommonRenderOptions::htmlLinkOption);
        $link->target="_dialog";
        $link->windowTitle='<img src="{{icon}}"/>- {{displayValue}} -';
        return $options;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {

        $version = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $jsRef= parent::getJsReferences(
            $document
        );

        $jsRef["testViewDocid"]="/TEST_DOCUMENT_SELENIUM/Layout/testViewDocid.js?ws=".$version;
        return $jsRef;
    }
}
