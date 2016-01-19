<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Test\Ddui;

use Dcp\AttributeIdentifiers\TST_DDUI_DOCID as myAttributes;

class DocidRenderConfigEdit extends \Dcp\Ui\DefaultEdit
{
    public function getLabel(\Doc $document = null)
    {
        return "Docid Edit";
    }
    
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        
        return $options;
    }
}

class DocidRenderConfigView extends \Dcp\Ui\DefaultView
{
    
    public function getLabel(\Doc $document = null)
    {
        return "Docid View";
    }
    
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        /**
         * @var $link \Dcp\Ui\HtmlLinkOptions
         */
        $link = $options->docid(myAttributes::test_ddui_docid__single2)->getOption(\Dcp\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir l'historique de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__single2)->setFormat('<i class="fa fa-history"/> {{displayValue}} (H)');
        
        $link = $options->docid(myAttributes::test_ddui_docid__single3)->getOption(\Dcp\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir les propriétés de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__single3)->setFormat("<i class=\"fa fa-info-circle\"/> {{displayValue}} (P)");
        
        $link = $options->docid(myAttributes::test_ddui_docid__multiple2)->getOption(\Dcp\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir l'historique de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__multiple2)->setFormat("<i class=\"fa fa-history\"/> {{displayValue}} (H)");
        
        $link = $options->docid(myAttributes::test_ddui_docid__multiple3)->getOption(\Dcp\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir les propriétés de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__multiple3)->setFormat("<i class=\"fa fa-info-circle\"/> {{displayValue}} (P)");
        
        $link = $options->docid(myAttributes::test_ddui_docid__single_link)->getOption(\Dcp\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir les propriétés de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__single_link)->setFormat("<i class=\"fa fa-info-circle\"/> {{displayValue}} (P)");
        
        $link = $options->docid(myAttributes::test_ddui_docid__multiple_link)->getOption(\Dcp\Ui\CommonRenderOptions::htmlLinkOption);
        $link->title = "Voir les propriétés de <br/><b>\"{{displayValue}}\"<b>";
        $options->docid(myAttributes::test_ddui_docid__multiple_link)->setFormat("<i class=\"fa fa-info-circle\"/> {{displayValue}} (P)");

        //$link = $options->text(myAttributes::test_ddui_docid__histo1)->getOption(\Dcp\Ui\CommonRenderOptions::htmlLinkOption);
        $linkTitle = sprintf("Voir l'historique <br/><b>\"%s\"<b>", $document->getHtmlTitle($document->getRawValue(myAttributes::test_ddui_docid__single1)));
        $options->text(myAttributes::test_ddui_docid__histo1)->setFormat($linkTitle);
        //$options->text(myAttributes::test_ddui_docid__link_histo)->showEmptyContent("Voir l'historique");

        $options->text(myAttributes::test_ddui_docid__link_histo)->setFormat("Voir historique lien du lien particulier");

        $link = $options->docid()->getOption(\Dcp\Ui\CommonRenderOptions::htmlLinkOption);
        $link->target="_dialog";
        $link->windowTitle='<img src="{{icon}}"/>- {{displayValue}} -';
        return $options;
    }

    public function getJsReferences(\Doc $document = null)
    {

        $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        $jsRef= parent::getJsReferences(
            $document
        );

        $jsRef["testViewDocid"]="TEST_DOCUMENT_SELENIUM/Layout/testViewDocid.js?ws=".$version;
        return $jsRef;
    }
}
