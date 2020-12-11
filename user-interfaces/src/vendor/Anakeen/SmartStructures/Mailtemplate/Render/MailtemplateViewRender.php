<?php

namespace Anakeen\SmartStructures\Mailtemplate\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Mailtemplate as myAttributes;

class MailtemplateViewRender extends DefaultConfigViewRender
{
    use TmailtemplateRender;

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $js = $this->getCommonJsReference($document, $js);

        return $js;
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return \Anakeen\Ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $options->commonOption()->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::upPosition);
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
