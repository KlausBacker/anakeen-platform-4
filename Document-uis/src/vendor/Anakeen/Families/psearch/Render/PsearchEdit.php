<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

class PsearchEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }
}