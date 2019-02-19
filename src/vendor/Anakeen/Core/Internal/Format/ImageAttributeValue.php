<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\SEManager;

class ImageAttributeValue extends FileAttributeValue
{
    public $thumbnail = '';

    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v, \Anakeen\Core\Internal\SmartElement $doc, $index, $thumbnailSize = 48)
    {
        parent::__construct($oa, $v, $doc, $index);
        $fileLink = $doc->getFileLink($oa->id, $index, false, true, $v);
        if ($fileLink) {
            if ($thumbnailSize > 0) {
                $this->thumbnail = $this->getImageLink($oa, $doc, $index, $thumbnailSize);
            } else {
                $this->thumbnail = $fileLink;
            }
        } elseif ($v) {
            global $action;
            $localImage = $action->parent->getImageLink($v);
            if ($localImage) {
                $this->displayValue = basename($v);
                $this->url = $localImage;
                if ($thumbnailSize > 0) {
                    $this->thumbnail = $action->parent->getImageLink($v, null, $thumbnailSize);
                } else {
                    $this->thumbnail = $localImage;
                }
            }
        }
    }

    protected function getImageLink($oa, $doc, $index, $width)
    {
        return sprintf(
            "api/v2/documents/%d/images/%s/%d/sizes/%sx%sc.png",
            $doc->id,
            rawurlencode($oa->id),
            $index,
            $width,
            $width
        );
    }
}
