<?php

namespace Anakeen\SmartStructures\FieldAccessLayer\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Dcp\Ui\RenderAttributeVisibilities;
use Dcp\Ui\RenderOptions;
use \SmartStructure\Fields\Fieldaccesslayer as myAttributes;

class FieldAccessLayerEditRender extends DefaultConfigEditRender
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

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null) : RenderAttributeVisibilities
    {
        $vis =  parent::getVisibilities($document, $mask);
        $vis->setVisibility(myAttributes::fal_fieldlabel, RenderAttributeVisibilities::StaticWriteVisibility);
        $vis->setVisibility(myAttributes::fal_fieldoriginalaccess, RenderAttributeVisibilities::StaticWriteVisibility);
        return $vis;
    }
}
