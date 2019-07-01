<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Mailtemplate\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Mailtemplate as myAttributes;

class MailtemplateEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $options->enum()->useFirstChoice(true);
        $options->enum(myAttributes::tmail_savecopy)->setDisplay('bool');
        $options->enum(myAttributes::tmail_ulink)->setDisplay('bool');
        $options->enum(myAttributes::tmail_savecopy)->displayDeleteButton(false);
        $options->enum(myAttributes::tmail_ulink)->displayDeleteButton(false);
        $options->enum(myAttributes::tmail_ulink)->displayDeleteButton(false);
        $options->arrayAttribute(myAttributes::tmail_t_from)->setRowMinDefault(1);
        $options->arrayAttribute(myAttributes::tmail_t_from)->setRowMaxLimit(1);
        $options->arrayAttribute(myAttributes::tmail_t_from)->disableRowAdd(true);
        $options->arrayAttribute(myAttributes::tmail_t_from)->disableRowDel(true);
        $options->arrayAttribute(myAttributes::tmail_t_from)->disableRowMove(true);

        $options->arrayAttribute(myAttributes::tmail_title)->setDescription(
            "<p>Name of the mail template</p>",
            \Anakeen\Ui\CommonRenderOptions::clickPosition
        );
        $options->arrayAttribute(myAttributes::tmail_family)->setDescription(
            "<p>SmartStructure linked to the mail template.</p><p>The associated Smart Fields can be used into 
the mail template.</p>",
            \Anakeen\Ui\CommonRenderOptions::clickPosition
        );
        $options->arrayAttribute(myAttributes::tmail_workflow)->setDescription(
            "<p>Link to the workflow where the mail template can be used</p>",
            \Anakeen\Ui\CommonRenderOptions::clickPosition
        );
        $options->arrayAttribute(myAttributes::tmail_t_from)->setDescription(
            "<p>Configuration parameters of the mail sender</p>",
            \Anakeen\Ui\CommonRenderOptions::clickPosition
        );
        $options->arrayAttribute(myAttributes::tmail_dest)->setDescription(
            "<p>Configuration parameters of the mail destination</p>",
            \Anakeen\Ui\CommonRenderOptions::clickPosition
        );
        $options->arrayAttribute(myAttributes::tmail_subject)->setDescription(
            "<p>Subject of the mail sent</p>",
            \Anakeen\Ui\CommonRenderOptions::clickPosition
        );
        $options->arrayAttribute(myAttributes::tmail_ulink)->setDescription(
            "<p>Uncheck option if you don't want to include links.</p>
<p>This way you can prevent users from getting links they are not allowed to navigate to.</p>",
            \Anakeen\Ui\CommonRenderOptions::clickPosition
        );
        $options->arrayAttribute(myAttributes::tmail_body)->setDescription(
            "<p>Inner content of the mail</p>
<p>Make sure to <b>preserve parameters formatting</b> (e.g. [PARAM])</p>",
            \Anakeen\Ui\CommonRenderOptions::topValuePosition
        );
        $options->arrayAttribute(myAttributes::tmail_savecopy)->setDescription(
            "<p>Check option if you want to save a copy of the mail</p>
<p><b>Warning</b> : This option can result in performance loss due to a large number of stored Smart Elements</p>",
            \Anakeen\Ui\CommonRenderOptions::clickPosition
        );

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
        $options->docid(myAttributes::tmail_workflow)->setInputTooltip(
            ___("Workflow Structure to use revision comment and transition parameters in body message", "mailtemplate")
        );
        return $options;
    }
}
