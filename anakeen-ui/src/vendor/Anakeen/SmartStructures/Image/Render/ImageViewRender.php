<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Image\Render;
use Anakeen\Ui\DefaultConfigViewRender;
use Dcp\Ui\RenderOptions;
use \SmartStructure\Fields\Image as myAttributes;

class ImageViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $options->image(myAttributes::img_file)->setThumbnailSize(800);
        return $options;
    }
}
