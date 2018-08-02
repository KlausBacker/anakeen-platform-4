<?php

namespace Anakeen\Ui;

use Anakeen\Core\Internal\ImportSmartConfiguration;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;

use Dcp\Ui\RenderConfigManager;
use SmartStructure\Fields\Cvdoc as CvDocFields;
use SmartStructure\Fields\Mask as MaskFields;

class ImportRenderConfiguration extends ImportSmartConfiguration
{
    protected function importConfigurations()
    {
        $data = $this->importMasks();
        $data = array_merge($data, $this->importCvDocs());
        $data = array_merge($data, $this->importStructureRender());

        $this->recordSmartData($data);
    }

    protected function importStructureRender()
    {
        $configs = $this->getNodes($this->dom->documentElement, "render");

        $data = [];
        /** @var \DOMElement $config */
        foreach ($configs as $config) {
            if ($ref = $config->getAttribute("ref")) {
                $cvdocRef = $this->evaluate($config, "string(ui:view-control/@ref)");

                $data[] = ["BEGIN", "", "", "", "", $ref];
                $data[] = ["CVDOC", $cvdocRef];
                $data[] = ["END"];

                $renderAccess = $this->evaluate($config, "string(ui:render-access/@class)");
                if ($renderAccess) {
                    RenderConfigManager::setRenderParameter($ref, "renderAccessClass", $renderAccess);
                }
                $disableEtag = $this->evaluate($config, "string(ui:render-access/@disable-etag)");
                if ($disableEtag) {
                    RenderConfigManager::setRenderParameter($ref, "disableEtag", $disableEtag==="true");
                }
            }
        }

        return $data;
    }

    protected function importMasks()
    {
        $configs = $this->getNodes($this->dom->documentElement, "mask");

        $data = [];
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importMask($config));
        }
        return $data;
    }

    protected function importCvDocs()
    {
        $configs = $this->getNodes($this->dom->documentElement, "view-control");

        $data = [];
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importCvDoc($config));
        }
        return $data;
    }

    protected function importMask(\DOMElement $maskNode)
    {
        $mask = SEManager::createDocument("MASK");

        $name = $maskNode->getAttribute("name");
        if ($name) {
            $mask->name = $name;

            $famid = $maskNode->getAttribute("structure");
            if ($famid) {
                $mask->setValue(MaskFields::msk_famid, $famid);
            }
            $label = $maskNode->getAttribute("label");
            if ($label) {
                $mask->setValue(MaskFields::ba_title, $label);
            }

            $visibilityNodes = $this->getNodes($maskNode, "visibility");
            $needNodes = $this->getNodes($maskNode, "need");

            $maskData = [];
            /**
             * @var \DOMElement $visibilityNode
             */
            foreach ($visibilityNodes as $visibilityNode) {
                $field = $visibilityNode->getAttribute("field");
                if ($field) {
                    $maskData[$field]['visibility'] = $visibilityNode->getAttribute("value");
                }
            }
            /**
             * @var \DOMElement $needNode
             */
            foreach ($needNodes as $needNode) {
                $field = $needNode->getAttribute("field");
                if ($field) {
                    $maskData[$field]['need'] = $needNode->getAttribute("value");
                }
            }
            foreach ($maskData as $aid => $maskDatum) {
                $mask->addArrayRow(
                    MaskFields::msk_t_contain,
                    [
                        MaskFields::msk_attrids => $aid,
                        MaskFields::msk_visibilities => isset($maskDatum["visibility"]) ? $maskDatum["visibility"] : '-',
                        MaskFields::msk_needeeds => isset($maskDatum["need"]) ? $maskDatum["need"] : '-'
                    ]
                );
            }
            return $this->getElementdata($mask);
        }
        return [];
    }


    protected function importCvDoc(\DOMElement $cvNode)
    {
        $cvdoc = SEManager::createDocument("CVDOC");

        $name = $cvNode->getAttribute("name");
        if ($name) {
            $cvdoc->name = $name;

            $famid = $cvNode->getAttribute("structure");
            if ($famid) {
                $cvdoc->setValue(CvDocFields::cv_famid, $famid);
            }
            $label = $cvNode->getAttribute("label");
            if ($label) {
                $cvdoc->setValue(CvDocFields::ba_title, $label);
            }
            $desc = $cvNode->getAttribute("description");
            if ($desc) {
                $cvdoc->setValue(CvDocFields::ba_desc, $desc);
            }
            $createvid = $this->evaluate($cvNode, "string(ui:creation-view/@ref)");
            if ($createvid) {
                $cvdoc->setValue(CvDocFields::cv_idcview, $createvid);
            }

            $viewNodes = $this->getNodes($cvNode, "view");
            /**
             * @var \DOMElement $viewNode
             */
            foreach ($viewNodes as $viewNode) {
                $mskid = $this->evaluate($viewNode, "string(ui:mask/@ref)");
                $rcClass = $this->evaluate($viewNode, "string(ui:render-config/@class)");

                $cvdoc->addArrayRow(
                    CvDocFields::cv_t_views,
                    [
                        CvDocFields::cv_idview => $viewNode->getAttribute("name"),
                        CvDocFields::cv_lview => $viewNode->getAttribute("label"),
                        CvDocFields::cv_kview => $viewNode->getAttribute("display-mode") === "edition" ? "VEDIT" : "VCONS",
                        CvDocFields::cv_order => $viewNode->getAttribute("order"),
                        CvDocFields::cv_menu => $viewNode->getAttribute("submenu-label"),
                        CvDocFields::cv_displayed => $viewNode->getAttribute("menu-displayed") === "false" ? "no" : "yes",

                        CvDocFields::cv_mskid => $mskid,
                        CvDocFields::cv_renderconfigclass => $rcClass,
                    ]
                );
            }

            return $this->getElementdata($cvdoc);
        }
        return [];
    }

    protected function getElementdata(SmartElement $elt)
    {
        $values = $elt->getValues();

        $order = ["ORDER", $elt->fromname, "", ""];
        $data = ["DOC", $elt->fromname, $elt->name, ""];
        foreach ($values as $aid => $value) {
            if ($value !== "") {
                $order[] = $aid;
                $data[] = $value;
            }
        }
        return ([$order, $data]);
    }

    /**
     * @param string      $name
     * @param \DOMElement $e
     *
     * @return \DOMNodeList
     */
    protected function getNodes(\DOMElement $e, $name)
    {
        return $e->getElementsByTagNameNS(ExportRenderConfiguration::NSUIURL, $name);
    }

    protected function evaluate(\DOMElement $e, $path)
    {
        $xpath = new \DOMXpath($this->dom);
        $xpath->registerNamespace("ui", ExportRenderConfiguration::NSUIURL);
        return $xpath->evaluate($path, $e);
    }
}
