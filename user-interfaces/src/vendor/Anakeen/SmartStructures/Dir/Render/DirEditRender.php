<?php

namespace Anakeen\SmartStructures\Dir\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Dir as myAttributes;

class DirEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        return $options;
    }
}
