<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use \SmartStructure\Attributes\Iuser as myAttributes;

class IuserEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->frame(myAttributes::us_fr_security)->setTemplate(
            <<< 'HTML'
            {{{attributes.us_accexpiredate.label}}} : {{{attributes.us_accexpiredate.htmlContent}}}
HTML
        );
        return $options;
    }
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document)
    {
        $visibilities = parent::getVisibilities($document);

        if (!$document->getRawValue(myAttributes::us_fr_security)) {
            $visibilities->setVisibility(myAttributes::us_fr_security, \Dcp\Ui\RenderAttributeVisibilities::ReadWriteVisibility);
        }
        return $visibilities;
    }
}