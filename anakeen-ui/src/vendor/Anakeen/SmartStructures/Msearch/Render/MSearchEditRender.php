<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Msearch\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Dcp\Ui\RenderOptions;
use \SmartStructure\Fields\Msearch as myAttributes;

class MSearchEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        return $options;
    }
}