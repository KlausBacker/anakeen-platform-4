<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;
use SmartStructure\Attributes\CVDOC as myAttributes;

class CvdocViewRender extends DefaultConfigViewRender
{
    /**
     * @param \Doc $document Document instance
     *
     * @return \Dcp\Ui\RenderOptions
     */
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
        return $options;
    }
    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"] = array(
            "file" => __DIR__.'/CvdocView.mustache'
        );
        return $templates;
    }
//
//    public function getJsReferences(\Doc $document = null)
//    {
//        $js = parent::getJsReferences();
//        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
//        $js["dduiCvdoc"] = "DOCUMENT/Layout/Cvdoc/Cvdoc.js?ws=" . $version;
//        return $js;
//    }
    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences();
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css["dduiCvdoc"] = "DOCUMENT/Layout/Cvdoc/Cvdoc.css?ws=" . $version;
        return $css;
    }
}
