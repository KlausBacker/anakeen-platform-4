<?php

namespace Anakeen\Core\Internal\Format;

class FileAttributeValue extends StandardAttributeValue
{
    public $size = 0;
    public $creationDate = '';
    public $fileName = '';
    public $url = '';
    public $mime = '';
    public $icon = '';

    /**
     * @noinspection PhpMissingParentConstructorInspection
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $oa
     * @param                                              $v
     * @param \Anakeen\Core\Internal\SmartElement          $doc
     * @param                                              $index
     * @param int                                          $iconMimeSize
     */
    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v, \Anakeen\Core\Internal\SmartElement $doc, $index, $iconMimeSize = 24)
    {
        $this->value = ($v === '') ? null : $v;
        if ($v) {
            $finfo = $doc->getFileInfo($v, "", "object");
            if ($finfo) {
                $this->size = $finfo->size;
                $this->creationDate = $finfo->cdate;
                $this->fileName = $finfo->name;
                $this->mime = $finfo->mime_s;
                $this->displayValue = $this->fileName;

                $iconFile = \Anakeen\Core\Utils\FileMime::getIconMimeFile($this->mime);
                if ($iconFile) {
                    $this->icon = $doc->getIcon($iconFile, $iconMimeSize);
                }
                $this->url = $doc->getFileLink($oa->id, $index, false, true, $v, $finfo);
            }
        }
    }
}
