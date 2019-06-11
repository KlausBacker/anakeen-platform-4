<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Wdoc\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use Anakeen\Ui\RenderOptions;

class WdocViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        return $options;
    }
}
