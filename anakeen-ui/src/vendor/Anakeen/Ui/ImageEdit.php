<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;
use \SmartStructure\Attributes\IMAGE as myAttributes;

class ImageEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::img_catg)->setDisplay('vertical');
        return $options;
    }

}
