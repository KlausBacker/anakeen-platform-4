<?php

namespace Anakeen\SmartStructures\Cvdoc\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use SmartStructure\Attributes\Cvdoc as myAttributes;

class CvdocViewRender extends DefaultConfigViewRender
{
    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->arrayAttribute(myAttributes::cv_t_views)->setTemplate(file_get_contents(__DIR__."/CvdocTable.mustache"));
        return $options;
    }
    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"] = array(
            "file" => __DIR__.'/CvdocView.mustache'
        );
        return $templates;
    }
}
