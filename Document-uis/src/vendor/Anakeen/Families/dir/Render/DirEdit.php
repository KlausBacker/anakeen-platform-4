<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;
use \Dcp\AttributeIdentifiers\DIR as myAttributes;

class DirEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::gui_isrss)->setDisplay('bool');
        $options->enum(myAttributes::gui_isrss)->displayDeleteButton(false);
        return $options;
    }
}