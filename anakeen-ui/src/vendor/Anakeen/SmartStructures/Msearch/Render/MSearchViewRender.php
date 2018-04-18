<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Msearch\Render;

use Anakeen\Ui\DefaultConfigViewRender;

class MSearchViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }
}