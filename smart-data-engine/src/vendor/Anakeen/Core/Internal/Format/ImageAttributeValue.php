<?php

namespace Anakeen\Core\Internal\Format;

use Anakeen\Core\Settings;

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
            if ($thumbnailSize > 0) {
                $this->thumbnail = sprintf(
                    "%simages/assets/sizes/%dx%dc/%s",
                    Settings::ApiV2,
                    $thumbnailSize,
                    $thumbnailSize,
                    $v
                );
            } else {
                $this->thumbnail = sprintf("%simages/assets/sizes/original/%s", Settings::ApiV2, $v);
            }
        }
    }

    protected function getImageLink($oa, $doc, $index, $width)
    {
        return sprintf(
            "/api/v2/smart-elements/%d/images/%s/%d/sizes/%sx%sc.png",
            $doc->id,
            rawurlencode($oa->id),
            $index,
            $width,
            $width
        );
    }
}
