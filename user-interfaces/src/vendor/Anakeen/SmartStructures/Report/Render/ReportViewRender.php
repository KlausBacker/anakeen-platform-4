<?php

namespace Anakeen\SmartStructures\Report\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\SmartStructures\Dsearch\Render\SearchViewRender;
use Anakeen\Ui\BarMenu;
use Anakeen\Ui\DefaultView;
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
        $menu = parent::getMenu($document);

        $menu->removeElement("advanced");
        $menu->removeElement("searchview");

        return $menu;
    }

    public function getJsReferences(SmartElement $document = null)
    {
        $js = parent::getJsReferences($document);

        $js["kendoDLL"] = UIGetAssetPath::getJSKendoComponentPath();
        $js["dSearch"] = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev": "legacy")["Dsearch"]["js"];

        return $js;
    }


    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["ankcomponent"]= \Anakeen\Ui\UIGetAssetPath::getCssSmartWebComponents();
        return $css;
    }
}
