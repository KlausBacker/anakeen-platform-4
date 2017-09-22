<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use \Dcp\AttributeIdentifiers\WDOC as myAttributes;

class WdocEditRender extends defaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }

}
