<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

class RoleViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }
}