<?php

namespace Anakeen\Ui;

use Anakeen\Core\SEManager;
use Dcp\Ui\RenderConfigManager;
use SmartStructure\Fields\Cvdoc as CvDocFields;
use SmartStructure\Fields\Mask as MaskFields;
use SmartStructure\Mask;

/**
 * Class ExportRenderConfiguration
 *
 * Export Smart Structure Render in Xml
 */
class ExportRenderConfiguration extends \Anakeen\Core\SmartStructure\ExportConfigurationAccesses
{
    const NSUIURL = self::NSBASEURL."ui/1.0";
    const NSUI = "ui";

    protected function extract($structConfig)
    {
        $this->domConfig->setAttribute("xmlns:ui", self::NSUIURL);
        $this->extractCv($this->domConfig);
    }

    protected function extractCv(\DOMElement $structConfig)
    {
        $access = $this->celui("render");
        $access->setAttribute("ref", $this->sst->name);
        $class = RenderConfigManager::getRenderParameterAccess($this->sst->name);
        if ($class) {
            $tag = $this->celui("render-access");
            $tag->setAttribute("class", $class);
            $access->appendChild($tag);
        }
        if ($this->sst->ccvid) {
            $tag = $this->celui("view-control");
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
        $cvtag = $this->celui("view-control");

        $cvtag->setAttribute("name", $cvdoc->name ?: $cvdoc->id);
        $cvtag->setAttribute("label", $cvdoc->title);

        $cvtag->setAttribute("structure", self::getLogicalName($cvdoc->getRawvalue(CvDocFields::cv_famid)));

        $desc = $cvdoc->getRawValue(CvDocFields::ba_desc);
        if ($desc) {
            $cvdesc = $this->celui("description");
            $cvdesc->appendChild($this->dom->createCDATASection($desc));
            $cvtag->appendChild($cvdesc);
        }
        $primaryMask = $cvdoc->getRawValue(CvDocFields::cv_primarymask);
        if ($primaryMask) {
            $primaryMskNode = $this->celui("primary-mask");
            $primaryMskNode->setAttribute("ref", static::getLogicalName($primaryMask));
            $cvtag->appendChild($primaryMskNode);
            /**
             * @var Mask $mask
             */
            $mask=SEManager::getDocument($primaryMask);
            $this->domConfig->appendChild($this->extractMaskData($mask));
            $this->setAccessProfile($mask);
        }
        $idcview = $cvdoc->getRawValue(CvDocFields::cv_idcview);
        if ($idcview) {
            $idcviewtag = $this->celui("creation-view");
            $idcviewtag->setAttribute("ref", $idcview);
            $cvtag->appendChild($idcviewtag);
        }
        $accessClass = $cvdoc->getRawValue(CvDocFields::cv_renderaccessclass);
        if ($accessClass) {
            $accessClassTag = $this->celui("render-access");
            $accessClassTag->setAttribute("class", $accessClass);
            $cvtag->appendChild($accessClassTag);
        }
        $views = $cvdoc->getAttributeValue(CvDocFields::cv_t_views);

        $viewlist = $this->celui("view-list");

        foreach ($views as $view) {
            $viewtag = $this->celui("view");
            $viewtag->setAttribute("name", $view[CvDocFields::cv_idview]);
            $viewtag->setAttribute("label", $view[CvDocFields::cv_lview]);
            $viewtag->setAttribute("display-mode", $view[CvDocFields::cv_kview]==="VEDIT"?"edition":"consultation");
            if ($view[CvDocFields::cv_mskid]) {
                $msktag = $this->celui("mask");
                $msktag->setAttribute("ref", static::getLogicalName($view[CvDocFields::cv_mskid]));
                $viewtag->appendChild($msktag);
                /**
                 * @var \SmartStructure\Mask $mask
                 */
                $mask = SEManager::getDocument($view[CvDocFields::cv_mskid]);
                $this->domConfig->appendChild($this->extractMaskData($mask));
                $this->setAccessProfile($mask);
            }
            if ($view[CvDocFields::cv_renderconfigclass]) {
                $rcctag = $this->celui("render-config");
                $rcctag->setAttribute("class", $view[CvDocFields::cv_renderconfigclass]);
                $viewtag->appendChild($rcctag);
            }
            if ($view[CvDocFields::cv_order]) {
                $viewtag->setAttribute("order", intval($view[CvDocFields::cv_order]));
            }
            $viewtag->setAttribute("menu-displayed", ($view[CvDocFields::cv_displayed] === "yes") ? "true" : "false");
            if ($view[CvDocFields::cv_menu]) {
                $viewtag->setAttribute("submenu-label", $view[CvDocFields::cv_menu]);
            }
            $viewlist->appendChild($viewtag);
        }
        $cvtag->appendChild($viewlist);

        return $cvtag;
    }

    protected function extractMaskData(\SmartStructure\Mask $mask)
    {
        $masktag = $this->celui("mask");

        $masktag->setAttribute("name", $mask->name ?: $mask->id);
        $masktag->setAttribute("label", $mask->title);
        $masktag->setAttribute("structure", self::getLogicalName($mask->getRawvalue(MaskFields::msk_famid)));
        $views = $mask->getAttributeValue(MaskFields::msk_t_contain);

        $visList = $this->celui("visibility-list");
        $masktag->appendChild($visList);
        $needList = $this->celui("need-list");
        $masktag->appendChild($needList);


        foreach ($views as $data) {
            $vis = $data[MaskFields::msk_visibilities];
            if ($vis && $vis !== "-") {
                $dataTag = $this->celui("visibility");
                $dataTag->setAttribute("field", $data[MaskFields::msk_attrids]);
                $dataTag->setAttribute("value", $vis);
                $visList->appendChild($dataTag);
            }
            $need = $data[MaskFields::msk_needeeds];
            if ($need && $need !== "-") {
                $dataTag = $this->celui("need");
                $dataTag->setAttribute("field", $data[MaskFields::msk_attrids]);
                $dataTag->setAttribute("value", ($need === "Y") ? "true" : "false");
                $needList->appendChild($dataTag);
            }
        }
        return $masktag;
    }

    protected function celui($name)
    {
        return $this->dom->createElement(self::NSUI . ":" . $name);
        //return $this->dom->createElementNS(self::NSUIURL, self::NSUI . ":" . $name);
    }
}
