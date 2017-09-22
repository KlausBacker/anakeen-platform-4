<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use \Dcp\AttributeIdentifiers\MSEARCH as myAttributes;

class MSearchEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::gui_isrss)->setDisplay('bool');
        $options->enum(myAttributes::se_memo)->setDisplay('bool');
        $options->enum(myAttributes::gui_isrss)->displayDeleteButton(false);
        $options->enum(myAttributes::se_memo)->displayDeleteButton(false);
        return $options;
    }
}