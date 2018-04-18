<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Wdoc\Render;

use Anakeen\Ui\DefaultConfigViewRender;

class WdocViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }
}
