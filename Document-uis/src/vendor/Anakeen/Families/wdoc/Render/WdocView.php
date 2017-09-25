<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

class WdocViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }
}
