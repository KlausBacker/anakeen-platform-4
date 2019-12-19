<?php

namespace Anakeen\SmartStructures\Report\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\SmartStructures\Dsearch\Render\SearchViewRender;
use Anakeen\Ui\BarMenu;
use Anakeen\Ui\DefaultView;
use Anakeen\Ui\ItemMenu as ItemMenu;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Report;

class ReportViewRender extends DefaultView
{

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"]["file"]
            = __DIR__ . "/reportHTML5_view.mustache";
        return $templates;
    }

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document):BarMenu
    {
        $myMenu = parent::getMenu($document);

        $searchViewItem = new ItemMenu("searchview");
        $searchViewItem->setTextLabel(___("consult", "searchUi"));
        $searchViewItem->setUrl("#action/previewConsult");
        $myMenu->appendElement($searchViewItem);

        $exportViewItem = new ItemMenu("exportView");
        $exportViewItem->setBeforeContent('<i class="fa fa-upload"></i>');
        $exportViewItem->setTextLabel(___("export", "searchUi"));
        $exportViewItem->setUrl("#action/exportReport");
        $myMenu->appendElement($exportViewItem);

        $myMenu->removeElement("se_open");
        $myMenu->removeElement("advanced");
        $myMenu->removeElement("searchview");

        return $myMenu;
    }

    public function getJsReferences(SmartElement $document = null)
    {
        $js = parent::getJsReferences($document);
        $js["dSearch"] = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev": "prod")["Dsearch"]["js"];

        return $js;
    }


    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["ankcomponent"]= \Anakeen\Ui\UIGetAssetPath::getCssSmartWebComponents();
        return $css;
    }
}
