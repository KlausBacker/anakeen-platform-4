<?php

namespace Anakeen\Ui;

use Anakeen\Core\SEManager;
use SmartStructure\Fields\Cvdoc as CvDocFields;
use SmartStructure\Mask;

/**
 * Class ExportRenderConfiguration
 *
 * Export Smart Structure Render in Xml
 */
class ExportRenderAccessConfiguration extends ExportRenderConfiguration
{
    protected $extractedDataAccess = [];

    protected function extract(\DOMElement $structConfig)
    {
        $this->domConfig->setAttribute("xmlns:" . self::NSUI, self::NSUIURL);
        $this->extractCvAccess($this->domConfig);
    }


    protected function extractCvAccess(\DOMElement $structConfig)
    {
        $access = $this->celui("render");
        $access->setAttribute("ref", $this->sst->name);

        if ($this->sst->ccvid) {
            /**
             * @var \SmartStructure\Cvdoc $cvdoc
             */
            $cvdoc = SEManager::getDocument($this->sst->ccvid);

            $this->extractCvdocDataAccess($cvdoc);

            $accessControl = $this->setAccess($this->sst->ccvid);
            $this->domConfig->appendChild($accessControl);
            $structConfig->appendChild($access);
        }
    }

    protected function extractCvdocDataAccess(\SmartStructure\Cvdoc $cvdoc)
    {
        if (isset($this->extractedDataAccess[$cvdoc->id])) {
            return null;
        }
        $this->extractedDataAccess[$cvdoc->id] = true;

        $primaryMask = $cvdoc->getRawValue(CvDocFields::cv_primarymask);
        if ($primaryMask) {
            /**
             * @var Mask $mask
             */
            $mask = SEManager::getDocument($primaryMask);
            if ($mask) {
                $this->setAccessProfile($mask);
            }
        }

        $views = $cvdoc->getAttributeValue(CvDocFields::cv_t_views);

        foreach ($views as $view) {
            if ($view[CvDocFields::cv_mskid]) {
                /**
                 * @var \SmartStructure\Mask $mask
                 */
                $mask = SEManager::getDocument($view[CvDocFields::cv_mskid]);
                if ($mask) {
                    $this->setAccessProfile($mask);
                }
            }
        }

    }

}
