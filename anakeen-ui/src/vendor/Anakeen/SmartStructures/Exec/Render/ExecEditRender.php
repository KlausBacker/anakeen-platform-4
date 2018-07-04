<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Exec\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Dcp\Ui\RenderOptions;

class ExecEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        return $options;
    }

}
