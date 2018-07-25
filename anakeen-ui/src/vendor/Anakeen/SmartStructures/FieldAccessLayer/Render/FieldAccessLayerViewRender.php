<?php

namespace Anakeen\SmartStructures\FieldAccessLayer\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use Dcp\Ui\RenderOptions;
use \SmartStructure\Fields\Fieldaccesslayer as myAttributes;

class FieldAccessLayerViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);


        return $options;
    }
}
