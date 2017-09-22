<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;
use \Dcp\AttributeIdentifiers\IMAGE as myAttributes;

class ImageEditRender extends defaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::img_catg)->setDisplay('vertical');
        return $options;
    }

}
