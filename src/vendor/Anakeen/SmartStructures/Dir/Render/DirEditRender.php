<?php

namespace Anakeen\SmartStructures\Dir\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Dcp\Ui\RenderOptions;
use \SmartStructure\Fields\Dir as myAttributes;

class DirEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::gui_isrss)->setDisplay('bool');
        $options->enum(myAttributes::gui_isrss)->displayDeleteButton(false);
        return $options;
    }
}
