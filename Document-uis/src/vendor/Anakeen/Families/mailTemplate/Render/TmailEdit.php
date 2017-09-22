<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use Dcp\AttributeIdentifiers\MAILTEMPLATE as myAttributes;

class TmailEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::tmail_savecopy)->setDisplay('bool');
        $options->enum(myAttributes::tmail_ulink)->setDisplay('bool');
        $options->enum(myAttributes::tmail_savecopy)->displayDeleteButton(false);
        $options->enum(myAttributes::tmail_ulink)->displayDeleteButton(false);
        $options->arrayAttribute(myAttributes::tmail_t_from)->setRowMinDefault(1);
        $options->frame(myAttributes::tmail_fr_content)->setTemplate(
            <<< 'HTML'
            <div>
                {{{attributes.tmail_body.htmlContent}}}
            </div>
            <div>
                {{{attributes.tmail_t_attach.htmlView}}}
            </div>
            <hr>
                {{{attributes.tmail_savecopy.htmlView}}}
                {{{attributes.tmail_ulink.htmlView}}}
HTML
        );
        return $options;
    }
}
