<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Cvdoc\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use SmartStructure\Attributes\Cvdoc as myAttributes;

class CvdocEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->arrayAttribute(myAttributes::cv_t_views)->setTemplate(file_get_contents(__DIR__."/CvdocTable.mustache"));
        $options->enum(myAttributes::cv_kview)->setDisplay('vertical');
        $options->enum(myAttributes::cv_displayed)->setDisplay('bool');
        $options->enum(myAttributes::cv_displayed)->displayDeleteButton(false);
        return $options;
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     *
     * @return \Dcp\Ui\RenderAttributeVisibilities new attribute visibilities
     * @throws \Dcp\Ui\Exception
     */
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document)
    {
        $visibilities = parent::getVisibilities($document);

        if (!$document->getRawValue(myAttributes::cv_famid)) {
            $visibilities->setVisibility(myAttributes::cv_famid, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        }
        return $visibilities;
    }
}
