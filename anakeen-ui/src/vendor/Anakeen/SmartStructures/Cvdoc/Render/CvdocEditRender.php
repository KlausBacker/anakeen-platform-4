<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Cvdoc\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use SmartStructure\Fields\Cvdoc as myAttributes;

class CvdocEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->arrayAttribute(myAttributes::cv_t_views)->setTemplate(file_get_contents(__DIR__."/CvdocTable.mustache"));
        $options->enum(myAttributes::cv_kview)->setDisplay('vertical');
        $options->enum(myAttributes::cv_displayed)->setDisplay('bool');
        $options->enum(myAttributes::cv_displayed)->displayDeleteButton(false);
        $options->int(myAttributes::cv_order)->setInputTooltip(xml_entity_encode(
            ___("Order for control access"," smart cvdoc")
        ));
        $options->text(myAttributes::cv_menu)->setInputTooltip(xml_entity_encode(
            ___("Label of menu list"," smart cvdoc")
        ));
        $options->docid(myAttributes::dpdoc_famid)->setInputTooltip(xml_entity_encode(
            ___("Structure used to configure access profil with account attributes"," smart cvdoc")
        ));
        $options->text(myAttributes::cv_renderaccessclass)->setInputTooltip(xml_entity_encode(
            ___("PHP Access render class used to fork the appropriate render config"," smart cvdoc")
        ));
        $options->text(myAttributes::cv_renderconfigclass)->setInputTooltip(xml_entity_encode(
            ___("PHP Config render class used to custom render"," smart cvdoc")
        ));
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
