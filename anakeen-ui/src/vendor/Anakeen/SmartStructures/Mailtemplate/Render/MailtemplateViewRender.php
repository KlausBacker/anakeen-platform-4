<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Mailtemplate\Render;
use Anakeen\Ui\DefaultConfigViewRender;
use SmartStructure\Attributes\Mailtemplate as myAttributes;

class MailtemplateViewRender extends DefaultConfigViewRender
{
    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->commonOption()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->frame(myAttributes::tmail_fr_content)->setTemplate(
            <<< 'HTML'
            <div>
                {{{attributes.tmail_body.htmlContent}}}
            </div>
            <div>
                {{{attributes.tmail_t_attach.htmlView}}}
            </div>
            <hr>
            <div class="panel-heading dcpArray__label dcpLabel">
                {{{attributes.tmail_savecopy.label}}} :{{{attributes.tmail_savecopy.htmlContent}}}
                {{{attributes.tmail_ulink.label}}} :{{{attributes.tmail_ulink.htmlContent}}}
            </div>
HTML
        );
        return $options;
    }
}
