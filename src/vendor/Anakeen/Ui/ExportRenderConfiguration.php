<?php

namespace Anakeen\Ui;

use Anakeen\Core\SEManager;
use Anakeen\Workflow\ExportElementConfiguration;
use Anakeen\Ui\RenderConfigManager;

/**
 * Class ExportRenderConfiguration
 *
 * Export Smart Structure Render in Xml
 */
class ExportRenderConfiguration extends \Anakeen\Core\SmartStructure\ExportConfigurationAccesses
{
    const NSUIURL = self::NSBASEURL . "ui/1.0";
    const NSUI = "ui";

    protected $extractedData = [];

    public function extract()
    {
        $this->domConfig->setAttribute("xmlns:" . self::NSUI, self::NSUIURL);
        $this->extractCvRef();
        $this->extractDefaultCvData();
    }

    public function extractCvRef()
    {
        $this->domConfig->setAttribute("xmlns:" . self::NSUI, self::NSUIURL);
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

            $this->setComment("Ui render configuration");
            $this->domConfig->appendChild($access);
        }
    }

    public function extractDefaultCvData()
    {
        if ($this->sst->ccvid) {
            $this->domConfig->setAttribute("xmlns:" . self::NSUI, self::NSUIURL);
            /**
             * @var \SmartStructure\Cvdoc $cvdoc
             */
            $cvdoc = SEManager::getDocument($this->sst->ccvid);

            $cvData = $this->extractCvdocData($cvdoc);
            if ($cvData) {
                $this->domConfig->appendChild($cvData);
            }
        }
    }
    protected function extractCvdocData(\SmartStructure\Cvdoc $cvdoc)
    {
        if (isset($this->extractedData[$cvdoc->id])) {
            return null;
        }

        $this->extractedData[$cvdoc->id] = true;
        return ExportElementConfiguration::getCvdocData($cvdoc->id, true, $this->dom);
    }

    protected function extractMaskData(\SmartStructure\Mask $mask)
    {
        if (isset($this->extractedData[$mask->id])) {
            return null;
        }
        $this->extractedData[$mask->id] = true;

        return ExportElementConfiguration::getMaskData($mask->id, $this->dom);
    }

    protected function celui($name)
    {
        return $this->dom->createElement(self::NSUI . ":" . $name);
        //return $this->dom->createElementNS(self::NSUIURL, self::NSUI . ":" . $name);
    }
}
