<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Image\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Image as myAttributes;

class ImageEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        return $options;
    }
}
