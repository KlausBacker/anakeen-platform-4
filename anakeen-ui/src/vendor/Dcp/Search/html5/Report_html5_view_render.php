<?php


namespace Dcp\Search\html5;

use SmartStructure\Attributes\Report;

class Report_html5_view_render extends Search_html5_view_render {

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null){
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"]["file"]
            = __DIR__."/reportHTML5_view.mustache";
        return $templates;
    }


    public function getMenu(\Anakeen\Core\Internal\SmartElement $document)
    {
        $menu= parent::getMenu($document);

        $printMenu=$menu->getElement(Report::rep_imp);
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
        $js= parent::getJsReferences($document);

        return $js;
    }
}