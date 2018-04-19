<?php

namespace Anakeen\SmartStructures\Report\Render;

use Anakeen\SmartStructures\Dsearch\Render\SearchViewRender;
use SmartStructure\Attributes\Report;

class ReportViewRender extends SearchViewRender
{

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"]["file"]
            = __DIR__ . "/reportHTML5_view.mustache";
        return $templates;
    }

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document)
    {
        $menu = parent::getMenu($document);

        $printMenu = $menu->getElement(Report::rep_imp);
        if ($printMenu) {
            $printMenu->setTarget("_blank");
        }


        $menu->removeElement(Report::se_openfolio);
        $menu->removeElement(Report::se_setsysrss);
        $menu->removeElement("advanced");
        $menu->removeElement("searchview");

        return $menu;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences($document);

        return $js;
    }
}
