<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DDUI
*/

namespace Dcp\Ui;

use Dcp\AttributeIdentifiers\HELPPAGE as myAttributes;
class HelpEditRender extends \Dcp\Ui\DefaultEdit
{
    
    public function getLabel(\Doc $document = null)
    {
        return "Help edit";
    }
    /**
     * @param \Doc $document Document instance
     *
     * @return RenderOptions
     */
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        
        $options->arrayAttribute(myAttributes::help_t_sections)->setTemplate(<<< 'HTML'
                    <table class="dcpArray__table">
                        <thead>
                            <tr>
                                {{#attribute.toolsEnabled}}<th></th>{{/attribute.toolsEnabled}}
                                <th style="width:200px">
                                    {{attributes.help_sec_order.label}}<br/>
                                    {{attributes.help_sec_lang.label}}<br/>
                                    {{attributes.help_sec_name.label}}<br/>
                                    {{attributes.help_sec_key.label}}
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
                                    {{{content.help_sec_name.htmlContent}}}<br/>
                                    {{{content.help_sec_key.htmlContent}}}
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

HTML) ;
        return $options;
    }
    /**
     * @param \Doc $document
     *
     * @return RenderAttributeVisibilities new attribute visibilities
     */
    public function getVisibilities(\Doc $document)
    {
        $visibilities = parent::getVisibilities($document);
        
        if (!$document->getRawValue(myAttributes::help_family)) {
            $visibilities->setVisibility(myAttributes::help_family, RenderAttributeVisibilities::ReadWriteVisibility);
        }
        return $visibilities;
    }
}
