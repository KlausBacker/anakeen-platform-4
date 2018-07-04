<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Dir\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use Dcp\Ui\RenderOptions;

class DirViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        return $options;
    }
}