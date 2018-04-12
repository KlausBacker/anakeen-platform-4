<?php

namespace Anakeen\SmartStructures\UiTest\TestRender\Renders;

use SmartStructure\Attributes\Tst_render as myAttributes;

class RenderConfigEdit extends \Dcp\Ui\DefaultEdit
{
    public function getLabel(\Doc $document = null)
    {
        return __METHOD__;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);


        $options->htmltext(myAttributes::tst_desc)->setToolbar(\Dcp\Ui\HtmltextRenderOptions::basicToolbar);


        return $options;
    }
}
