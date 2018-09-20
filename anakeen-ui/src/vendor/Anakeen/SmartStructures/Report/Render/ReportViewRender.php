<?php

namespace Anakeen\SmartStructures\Report\Render;

use Anakeen\SmartStructures\Dsearch\Render\SearchViewRender;
use Dcp\Ui\BarMenu;
use SmartStructure\Fields\Report;

class ReportViewRender extends SearchViewRender
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


    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["ankcomponent"]= \Dcp\Ui\UIGetAssetPath::getCssSmartWebComponents();
        return $css;
    }

}
