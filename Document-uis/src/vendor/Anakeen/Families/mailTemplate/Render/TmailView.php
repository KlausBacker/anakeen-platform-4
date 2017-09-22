<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;
use Dcp\AttributeIdentifiers\MAILTEMPLATE as myAttributes;

class TmailViewRender extends DefaultConfigViewRender
{
    /**
     * @param \Doc $document Document instance
     *
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(\Doc $document)
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
    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences();
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css["dduiMailTemplate"] = "DOCUMENT/Layout/MailTemplate/tmail.css?ws=" . $version;
        return $css;
    }
}
