<?php

namespace Sample\BusinessApp\Renders;

class CommonView extends \Dcp\Ui\DefaultView
{
    use Common {
        Common::getOptions as getCommonOptions;
    }

    public function getLabel(\Doc $document = null)
    {
        return __CLASS__;
    }

    public function getOptions(\Doc $document)
    {
        $options = $this->getCommonOptions($document);
        return $options;
    }


    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js["cstb-common"] = "BUSINESS_APP/Families/Common/Renders/common.js"."?ws=$ws";
        return $js;
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
