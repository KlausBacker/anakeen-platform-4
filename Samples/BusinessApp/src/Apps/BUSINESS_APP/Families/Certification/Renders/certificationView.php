<?php

namespace Sample\BusinessApp\Renders;

use Dcp\AttributeIdentifiers\Ba_Certification as MyAttr;
use Dcp\Ui\ListMenu;


class CertificationView extends CommonView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->frame()->showEmptyContent("<div>Aucunes informations</div>");
        $options->arrayAttribute()->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);
        $options->htmltext()->setToolbar(\dcp\Ui\HtmltextRenderOptions::basicToolbar);


        $timeline = '<iframe class="timeline timeline--audit" src="?app=BUSINESS_APP&action=BA_TIMELINEAUDIT" />';
        $options->frame(MyAttr::cert_fr_timeline)->setTemplate($timeline)->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);


        $timeline = '<iframe class="timeline timeline--essai" src="?app=BUSINESS_APP&action=BA_TIMELINEESSAI" />';
        $options->frame(MyAttr::cert_efr_timeline)->setTemplate($timeline)->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);


        $timeline = '<iframe class="timeline timeline--comite" src="?app=BUSINESS_APP&action=BA_TIMELINECOMITE" />';
        $options->frame(MyAttr::cert_cfr_timeline)->setTemplate($timeline)->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);


        $options->int(MyAttr::cert_eesp)->setFormat("{{displayValue}} m²");
        return $options;
    }

    public function getVisibilities(\Doc $document)
    {
        $vis = parent::getVisibilities($document);
        /*
            $vis->setVisibility(MyAttr::cert_fr_rd, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
            $vis->setVisibility(MyAttr::cert_efr_rd, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
            $vis->setVisibility(MyAttr::cert_cfr_rd, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        */
        return $vis;
    }


    public function getMenu(\Doc $document)
    {
        $menu = parent::getMenu($document);

        $userId = getCurrentUser()->id;
        if ($userId != "1") {
            $listMenu = new ListMenu("workflow");


            $this->getWorkflowMenu($document, $listMenu);
            $items = $listMenu->getElements();
            foreach ($items as $item) {
                if ($item->getId() !== "workflowSep" && $item->getId() !== "workflowGraph" && $item->getId() !== "workflowDraw") {
                    $item->setHtmlAttribute("class", "menu--left");
                    $menu->insertBefore("workflow", $item);
                }

            }

            $menu->removeElement("workflow");

            $menu->removeElement("delete");
        }


        return $menu;
    }

    public function getCssReferences(\Doc $document = null)
    {

        $css = parent::getCssReferences($document);
        $ws = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css[__CLASS__] = "BUSINESS_APP/Families/Certification/Renders/certification.css" . "?ws=$ws";
        return $css;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $ws = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js[__CLASS__] = "BUSINESS_APP/Families/Certification/Renders/certification.js" . "?ws=$ws";
        return $js;
    }
}
