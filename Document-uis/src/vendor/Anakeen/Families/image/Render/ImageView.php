<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;
use \Dcp\AttributeIdentifiers\IMAGE as myAttributes;

class ImageViewRender extends defaultConfigViewRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->image(myAttributes::img_file)->setThumbnailSize(800);
        return $options;
    }
}
