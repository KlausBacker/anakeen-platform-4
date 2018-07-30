<?php

namespace Anakeen\Ui;

use Anakeen\Core\SEManager;
use SmartStructure\Fields\Cvdoc as CvDocFields;
use SmartStructure\Fields\Mask as MaskFields;

/**
 * Class ExportRenderConfiguration
 *
 * Export Smart Structure Render in Xml
 */
class ExportRenderConfiguration extends \Anakeen\Core\SmartStructure\ExportConfiguration
{
    protected function extract($structConfig)
    {
        $this->extractCv($structConfig);
    }


    protected function extractCv(\DOMElement $structConfig)
    {
        $access = $this->cel("render");
        if ($this->sst->ccvid) {
            $tag = $this->cel("view-control");
            $tag->setAttribute("ref", static::getLogicalName($this->sst->ccvid));
            $access->appendChild($tag);

            /**
             * @var \SmartStructure\Cvdoc $cvdoc
             */
            $cvdoc = SEManager::getDocument($this->sst->ccvid);

            $cvData = $this->extractCvdocData($cvdoc);
            $this->domConfig->appendChild($cvData);

            $accessControl = $this->setAccess($this->sst->ccvid);
            $this->domConfig->appendChild($accessControl);
            $structConfig->appendChild($access);
        }
    }

    protected function extractCvdocData(\SmartStructure\Cvdoc $cvdoc)
    {
        $cvtag = $this->cel("view-control");

        $cvtag->setAttribute("name", $cvdoc->name ?: $cvdoc->id);
        $cvtag->setAttribute("label", $cvdoc->title);

        $desc = $cvdoc->getRawValue(CvDocFields::ba_desc);
        if ($desc) {
            $cvdesc = $this->cel("description");
            $cvdesc->appendChild($this->dom->createCDATASection($desc));
            $cvtag->appendChild($cvdesc);
        }
        $primaryMask = $cvdoc->getRawValue(CvDocFields::cv_primarymask);
        if ($primaryMask) {
            $primaryMskNode = $this->cel("primary-mask");
            $primaryMskNode->setAttribute("ref", static::getLogicalName($primaryMask));
            $cvtag->appendChild($primaryMskNode);
        }
        $idcview = $cvdoc->getRawValue(CvDocFields::cv_idcview);
        if ($idcview) {
            $idcviewtag = $this->cel("creation-view");
            $idcviewtag->setAttribute("ref", $idcview);
            $cvtag->appendChild($idcviewtag);
        }
        $accessClass = $cvdoc->getRawValue(CvDocFields::cv_renderaccessclass);
        if ($accessClass) {
            $accessClassTag = $this->cel("render-access");
            $accessClassTag->setAttribute("class", $accessClass);
            $cvtag->appendChild($accessClassTag);
        }
        $views = $cvdoc->getAttributeValue(CvDocFields::cv_t_views);

        $viewlist = $this->cel("view-list");

        foreach ($views as $view) {
            $viewtag = $this->cel("view");
            $viewtag->setAttribute("name", $view[CvDocFields::cv_idview]);
            $viewtag->setAttribute("label", $view[CvDocFields::cv_lview]);
            if ($view[CvDocFields::cv_mskid]) {
                $msktag = $this->cel("mask");
                $msktag->setAttribute("ref", static::getLogicalName($view[CvDocFields::cv_mskid]));
                $viewtag->appendChild($msktag);
                /**
                 * @var \SmartStructure\Mask $mask
                 */
                $mask = SEManager::getDocument($view[CvDocFields::cv_mskid]);
                $this->domConfig->appendChild($this->extractMaskData($mask));
            }
            if ($view[CvDocFields::cv_renderconfigclass]) {
                $rcctag = $this->cel("render-config");
                $rcctag->setAttribute("class", $view[CvDocFields::cv_renderconfigclass]);
                $viewtag->appendChild($rcctag);
            }
            if ($view[CvDocFields::cv_order]) {
                $viewtag->setAttribute("order", intval($view[CvDocFields::cv_order]));
            }
            $viewtag->setAttribute("menu-displayed", ($view[CvDocFields::cv_displayed] === "yes") ? "true" : "false");
            if ($view[CvDocFields::cv_menu]) {
                $viewtag->setAttribute("submenu-ref", $view[CvDocFields::cv_menu]);
            }
            $viewlist->appendChild($viewtag);
        }
        $cvtag->appendChild($viewlist);

        return $cvtag;
    }

    protected function extractMaskData(\SmartStructure\Mask $mask)
    {
        $masktag = $this->cel("mask");

        $masktag->setAttribute("name", $mask->name ?: $mask->id);
        $masktag->setAttribute("label", $mask->title);
        $views = $mask->getAttributeValue(MaskFields::msk_t_contain);

        $visList = $this->cel("visibility-list");
        $masktag->appendChild($visList);
        $needList = $this->cel("need-list");
        $masktag->appendChild($needList);


        foreach ($views as $data) {
            $vis = $data[MaskFields::msk_visibilities];
            if ($vis && $vis !== "-") {
                $dataTag = $this->cel("visibility");
                $dataTag->setAttribute("field", $data[MaskFields::msk_attrids]);
                $dataTag->setAttribute("value", $vis);
                $visList->appendChild($dataTag);
            }
            $need = $data[MaskFields::msk_needeeds];
            if ($need && $need !== "-") {
                $dataTag = $this->cel("need");
                $dataTag->setAttribute("field", $data[MaskFields::msk_attrids]);
                $dataTag->setAttribute("value", ($need === "Y") ? "true" : "false");
                $needList->appendChild($dataTag);
            }
        }
        return $masktag;
    }
}
