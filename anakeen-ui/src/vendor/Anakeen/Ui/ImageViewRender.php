<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;
use \SmartStructure\Attributes\Image as myAttributes;

class ImageViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->image(myAttributes::img_file)->setThumbnailSize(800);
        return $options;
    }
}
