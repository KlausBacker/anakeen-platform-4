<?php

namespace Anakeen\SmartStructures\FieldAccessLayerList\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Dcp\Ui\RenderOptions;
use \SmartStructure\Fields\Fieldaccesslayerlist as myAttributes;

class FieldAccessLayerListEditRender extends DefaultConfigEditRender
{
    protected $defaultGroup;

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute()->setRowMinDefault(1);
        return $options;
    }


}
