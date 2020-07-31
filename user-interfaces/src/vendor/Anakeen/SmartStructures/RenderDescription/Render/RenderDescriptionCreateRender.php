<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\RenderDescription\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Renderdescription as DescriptionFields;

class RenderDescriptionCreateRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);
        return $options;
    }

    public function getVisibilities(
        \Anakeen\Core\Internal\SmartElement $document,
        \SmartStructure\Mask $mask = null
    ): RenderAttributeVisibilities {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(DescriptionFields::rd_t_fields, RenderAttributeVisibilities::HiddenVisibility);
        return $visibilities;
    }
}
