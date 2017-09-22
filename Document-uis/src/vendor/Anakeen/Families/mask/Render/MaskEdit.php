<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use \Dcp\AttributeIdentifiers\MASK as myAttributes;

class MaskEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->commonOption(myAttributes::msk_needeeds)->setInputTooltip('insert Y or N');
        return $options;
    }
}