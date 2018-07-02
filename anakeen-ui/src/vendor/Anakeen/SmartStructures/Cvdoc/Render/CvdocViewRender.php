<?php

namespace Anakeen\SmartStructures\Cvdoc\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Ui\DefaultConfigViewRender;
use SmartStructure\Fields\Cvdoc as myAttributes;

class CvdocViewRender extends DefaultConfigViewRender
{
    /**
     * @param SmartElement $document Document instance
     *
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->arrayAttribute(myAttributes::cv_t_views)->setTemplate(file_get_contents(__DIR__."/CvdocTable.mustache"));

        return $options;
    }
    public function getTemplates(SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"] = array(
            "file" => __DIR__.'/CvdocView.mustache'
        );
        return $templates;
    }
}
