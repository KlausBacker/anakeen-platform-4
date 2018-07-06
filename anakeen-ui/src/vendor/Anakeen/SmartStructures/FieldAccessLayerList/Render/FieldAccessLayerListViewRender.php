<?php

namespace Anakeen\SmartStructures\FieldAccessLayerList\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use Dcp\Ui\RenderOptions;
use \SmartStructure\Fields\Fieldaccesslayer as myAttributes;

class FieldAccessLayerListViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);


        return $options;
    }
}
