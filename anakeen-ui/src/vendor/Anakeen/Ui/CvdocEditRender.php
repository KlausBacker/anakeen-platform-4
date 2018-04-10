<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use SmartStructure\Attributes\CVDOC as myAttributes;

class CvdocEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->arrayAttribute(myAttributes::cv_t_views)->setTemplate(
            <<< 'HTML'
            <table class="dcpArray__table">
            <thead>
            <tr>
            {{#attribute.toolsEnabled}}<th></th>{{/attribute.toolsEnabled}}
                <th>Identification</th>
                <th>{{{attributes.cv_kview.label}}}</th>
                <th>Vue</th>
                <th>Affichage</th>
            </tr>   
            </thead>
            <tbody>
                {{#attribute.rows}}
                <tr>
                    {{#attribute.toolsEnabled}}<td>{{{rowTools}}}</td>{{/attribute.toolsEnabled}}
                    <td>
                        {{{attributes.cv_order.label}}} :{{{content.cv_order.htmlContent}}}
                        {{{attributes.cv_idview.label}}} :{{{content.cv_idview.htmlContent}}}
                        {{{attributes.cv_lview.label}}} :{{{content.cv_lview.htmlContent}}}
                    </td>
                    <td>
                        {{{attributes.cv_kview.label}}} :{{{content.cv_kview.htmlContent}}}
                    </td>
                    <td>
                        {{{attributes.cv_renderconfigclass.label}}} :{{{content.cv_renderconfigclass.htmlContent}}}
                        {{{attributes.cv_zview.label}}} :{{{content.cv_zview.htmlContent}}}
                        {{{attributes.cv_mskid.label}}} :{{{content.cv_mskid.htmlContent}}}
                    </td>
                    <td>
                        {{{attributes.cv_displayed.label}}} :{{{content.cv_displayed.htmlContent}}}
                        {{{attributes.cv_menu.label}}} :{{{content.cv_menu.htmlContent}}}
                    </td>
                </tr>
                {{/attribute.rows}}
            </tbody>
            </table>
            <div>
            {{{attribute.tableTools}}}
            </div>
HTML
        );
        $options->enum(myAttributes::cv_kview)->setDisplay('vertical');
        $options->enum(myAttributes::cv_displayed)->setDisplay('bool');
        $options->enum(myAttributes::cv_displayed)->displayDeleteButton(false);
        return $options;
    }
    /**
     * @param \Doc $document
     *
     * @return \Dcp\Ui\RenderAttributeVisibilities new attribute visibilities
     */
    public function getVisibilities(\Doc $document)
    {
        $visibilities = parent::getVisibilities($document);

        if (!$document->getRawValue(myAttributes::cv_famid)) {
            $visibilities->setVisibility(myAttributes::cv_famid, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        }
        return $visibilities;
    }
}
