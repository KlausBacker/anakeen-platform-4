<?php

namespace Anakeen\Pu\TestDocumentView\SmartStructureTest\Render;

use Anakeen\Ui\DocumentTemplateContext;

class TstDocumentViewEditRender extends \Anakeen\Ui\DefaultConfigEditRender
{
    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["footer"]["content"] = '<footer class="smart-element-footer">[[#testFooterMessage]][[testFooterMessage]][[/testFooterMessage]]</footer>';
        $templates["sections"]["menu"]["file"] = __DIR__ . '/tstDocumentViewRenderFile.mustache';
        return $templates;
    }

    public function getContextController(\Anakeen\Core\Internal\SmartElement $document): DocumentTemplateContext
    {
        $controller = parent::getContextController($document);
        $controller["testFooterMessage"] = $this->customClientData["charTestKey"];

        $controller["testMenuMessage"] = $this->customClientData["charTestKey"];
        return $controller;
    }
}
