<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use Dcp\AttributeIdentifiers\MAILTEMPLATE as myAttributes;

class TmailEditRender extends defaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::tmail_savecopy)->setDisplay('bool');
        $options->enum(myAttributes::tmail_ulink)->setDisplay('bool');
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
                <span class="dcp__array__caret fa fa-lg fa-caret-down" style="font-family: Helvetica.Arial.sans-serif; font-size:12px">Other</span>
            </div>
            <div class="dcpArray__content">
                {{{attributes.tmail_savecopy.htmlView}}}
                {{{attributes.tmail_ulink.htmlView}}}
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
