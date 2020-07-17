<?php

namespace Anakeen\SmartStructures\FieldAccessLayer\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Fieldaccesslayer as myAttributes;

class FieldAccessLayerEditRender extends DefaultConfigEditRender
{
    protected $defaultGroup;

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return \Anakeen\Ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $options->arrayAttribute()->setRowMinDefault(1);
        return $options;
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null) : RenderAttributeVisibilities
    {
        $vis =  parent::getVisibilities($document, $mask);
        return $vis;
    }
}
