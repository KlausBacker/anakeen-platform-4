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
                $this->thumbnail = sprintf('%s&width=%d', $fileLink, $thumbnailSize);
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
}
