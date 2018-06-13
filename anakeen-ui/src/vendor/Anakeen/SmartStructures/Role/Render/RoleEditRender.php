<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Role\Render;

use Anakeen\Ui\DefaultConfigEditRender;

class RoleEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }
}