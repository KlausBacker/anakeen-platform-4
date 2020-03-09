<?php

namespace Anakeen\Ui;

use Anakeen\Core\Internal\ImportSmartConfiguration;
use Anakeen\Core\SEManager;

use Anakeen\Core\Utils\Xml;
use Anakeen\Exception;
use Anakeen\Ui\RenderConfigManager;
use SmartStructure\Fields\Cvdoc as CvDocFields;
use SmartStructure\Fields\Mask as MaskFields;

class ImportRenderConfiguration extends ImportSmartConfiguration
{
    protected $uiPrefix = "ui";


    public function importData($xmlFile)
    {
        $this->dom = new \DOMDocument();
        $this->dom->load($xmlFile);

        $data = $this->importDataElements();
        $this->recordSmartData($data);
    }


    public function importRender($xmlFile)
    {
        $this->dom = new \DOMDocument();
        $this->dom->load($xmlFile);

        $data = $this->importStructureRender();
        $this->recordSmartData($data);
    }


    public function importAll($xmlFile)
    {
        $this->importData($xmlFile);
        parent::importAll($xmlFile);
        $this->importRender($xmlFile);
    }

    protected function importDataElements()
    {
        $this->uiPrefix = Xml::getPrefix($this->dom, ExportRenderConfiguration::NSUIURL);
        $data = $this->importMasks();
        $data = array_merge($data, $this->importCvDocs());
        return $data;
    }

    protected function importStructureRender()
    {
        $configs = $this->getUiNodes($this->dom->documentElement, "render");

        $data = [];
        /** @var \DOMElement $config */
        foreach ($configs as $config) {
            if ($ref = $config->getAttribute("ref")) {
                $cvdocRef = $this->evaluate($config, "string({$this->uiPrefix}:view-control/@ref)");

                $data[] = ["BEGIN", "", "", "", "", $ref];
                $data[] = ["CVID", $cvdocRef];
                $data[] = ["END"];

                $renderAccess = $this->evaluate($config, "string({$this->uiPrefix}:render-access/@class)");
                if ($renderAccess) {
                    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_\\\\]+$/', $renderAccess)) {
                        throw new Exception(sprintf("Class name syntax error for \"%s\" in render access class for \"%s\"", $renderAccess, $ref));
                    }
                    RenderConfigManager::setRenderParameter($ref, "renderAccessClass", $renderAccess);
                }
                $disableEtag = $this->evaluate($config, "string({$this->uiPrefix}:render-access/@disable-etag)");
                if ($disableEtag) {
                    RenderConfigManager::setRenderParameter($ref, "disableEtag", $disableEtag === "true");
                }
            }
        }

        return $data;
    }

    protected function importMasks()
    {
        $configs = $this->getUiNodes($this->dom->documentElement, "mask");

        $data = [];
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importMask($config));
        }
        return $data;
    }

    protected function importCvDocs()
    {
        $configs = $this->getUiNodes($this->dom->documentElement, "view-control");

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

            $visibilityNodes = $this->getUiNodes($maskNode, "visibility");
            $needNodes = $this->getUiNodes($maskNode, "need");

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
                    $needValue = $needNode->getAttribute("value");
                    if ($needValue === "true") {
                        $maskData[$field]['need'] = 'Y';
                    } elseif ($needValue === "false") {
                        $maskData[$field]['need'] = 'N';
                    }
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
            $this->verboseMessages[] = sprintf("[%s] Record \"%s\"", $mask->fromname, $mask->name);
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
            $desc = $this->getNode($cvNode, "description");
            if ($desc) {
                $cvdoc->setValue(CvDocFields::ba_desc, $desc->nodeValue);
            }

            $masterMask = $this->evaluate($cvNode, "string({$this->uiPrefix}:primary-mask/@ref)");
            if ($masterMask) {
                // Very subtil : Need to change type on the fly to not try to resolve logicalname
                $cvdoc->getAttribute(CvDocFields::cv_primarymask)->type = "text";
                $cvdoc->setValue(CvDocFields::cv_primarymask, $masterMask);
            }
            $createvid = $this->evaluate($cvNode, "string({$this->uiPrefix}:creation-view/@ref)");
            if ($createvid) {
                $cvdoc->setValue(CvDocFields::cv_idcview, $createvid);
            }

            $viewNodes = $this->getUiNodes($cvNode, "view");

            // Very subtil : Need to change type on the fly to not try to resolve logicalname
            $cvdoc->getAttribute(CvDocFields::cv_mskid)->type = "text";
            /**
             * @var \DOMElement $viewNode
             */
            foreach ($viewNodes as $viewNode) {
                $mskid = $this->evaluate($viewNode, "string({$this->uiPrefix}:mask/@ref)");
                $rcClass = $this->evaluate($viewNode, "string({$this->uiPrefix}:render-config/@class)");

                if ($mskid && !\CheckDoc::isWellformedLogicalName($mskid)) {
                    throw new Exception(sprintf("Mask ref \"%s\" is not valid", $mskid));
                }
                $cvdoc->addArrayRow(
                    CvDocFields::cv_t_views,
                    [
                        CvDocFields::cv_idview => $viewNode->getAttribute("name"),
                        CvDocFields::cv_lview => $viewNode->getAttribute("label"),
                        CvDocFields::cv_kview => $viewNode->getAttribute("display-mode") === "edition" ? "VEDIT" : "VCONS",
                        CvDocFields::cv_order => $viewNode->getAttribute("order"),
                        CvDocFields::cv_menu => $viewNode->getAttribute("parent-menu-id"),
                        CvDocFields::cv_displayed => $viewNode->getAttribute("menu-displayed") === "false" ? "no" : "yes",

                        CvDocFields::cv_mskid => $mskid,
                        CvDocFields::cv_renderconfigclass => $rcClass,
                    ]
                );
            }

            $this->verboseMessages[] = sprintf("[%s] Record \"%s\"", $cvdoc->fromname, $cvdoc->name);
            return $this->getElementdata($cvdoc);
        }
        return [];
    }

    /**
     * @param string $name
     * @param \DOMElement $e
     *
     * @return \DOMNodeList
     */
    protected function getUiNodes(\DOMElement $e, $name)
    {
        return $e->getElementsByTagNameNS(ExportRenderConfiguration::NSUIURL, $name);
    }

    protected function evaluate(\DOMElement $e, $path)
    {
        $xpath = new \DOMXpath($this->dom);
        $xpath->registerNamespace($this->uiPrefix, ExportRenderConfiguration::NSUIURL);
        return $xpath->evaluate($path, $e);
    }
}
