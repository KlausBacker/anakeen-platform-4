<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Helppage\Render;

use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Helppage as myAttributes;

class HelppageEditRender extends \Anakeen\Ui\DefaultEdit
{
    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return \Anakeen\Ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute(myAttributes::help_t_sections)->setTemplate(
            <<< 'HTML'
                    <table class="dcpArray__table">
                        <thead>
                            <tr>
                                {{#attribute.toolsEnabled}}<th></th>{{/attribute.toolsEnabled}}
                                <th style="width:200px">
                                    {{attributes.help_sec_order.label}}<br/>
                                    {{attributes.help_sec_lang.label}}<br/>
                                    {{attributes.help_sec_key.label}}<br/>
                                    {{attributes.help_sec_name.label}}
                                </th>
                                <th>
                                    {{attributes.help_sec_text.label}}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        {{#attribute.rows}}
                            <tr>
                                {{#attribute.toolsEnabled}}<td>{{{rowTools}}}</td>{{/attribute.toolsEnabled}}
                                <td>

                                    {{{content.help_sec_order.htmlContent}}}<br/>
                                    {{{content.help_sec_lang.htmlContent}}}<br/>
                                    {{{content.help_sec_key.htmlContent}}}<br/>
                                    {{{content.help_sec_name.htmlContent}}}
                                </td>
                                <td>
                                    {{{content.help_sec_text.htmlContent}}}
                                </td>
                            </tr>
                        {{/attribute.rows}}
                        </tbody>
                    </table>
                    <div>
                        {{{attribute.tableTools}}}
                    </div>
HTML
        ) ;

        $options->arrayAttribute(myAttributes::help_t_sections)->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::upPosition);
        $options->text(myAttributes::help_sec_key)->setPlaceHolder($document->getLabel(myAttributes::help_sec_key));
        $options->text(myAttributes::help_sec_lang)->setPlaceHolder($document->getLabel(myAttributes::help_sec_lang));
        $options->text(myAttributes::help_sec_name)->setPlaceHolder($document->getLabel(myAttributes::help_sec_name));
        $options->int(myAttributes::help_sec_order)->setPlaceHolder($document->getLabel(myAttributes::help_sec_order));
        return $options;
    }
    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     *
     * @return RenderAttributeVisibilities new attribute visibilities
     */
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null) : RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        
        if (!$document->getRawValue(myAttributes::help_family)) {
            $visibilities->setVisibility(myAttributes::help_family, RenderAttributeVisibilities::ReadWriteVisibility);
        }
        return $visibilities;
    }
}