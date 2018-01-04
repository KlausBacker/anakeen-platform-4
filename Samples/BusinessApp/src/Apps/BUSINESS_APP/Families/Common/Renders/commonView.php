<?php

namespace Sample\BusinessApp\Renders;

class CommonView extends \Dcp\Ui\DefaultView
{

    public function getLabel(\Doc $document = null)
    {
        return __CLASS__;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }


    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js["ba-common"] = "BUSINESS_APP/Families/Common/Renders/common.js"."?ws=$ws";
        return $js;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["ba-common"] = "./BUSINESS_APP/Families/Common/Renders/common.css";
        return $css;
    }


    /**
     * Get workflow submenu contents
     * @param \Doc $doc
     * @param \Dcp\Ui\ListMenu $menu
     */
    protected function getWorkflowMenu(\Doc $doc, \Dcp\Ui\ListMenu & $menu)
    {
        parent::getWorkflowMenu($doc, $menu);
        $menu->removeElement("workflowGraph");
    }

}
