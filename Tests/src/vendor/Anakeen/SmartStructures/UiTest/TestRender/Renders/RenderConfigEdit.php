<?php

namespace Anakeen\SmartStructures\UiTest\TestRender\Renders;

use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Tst_render as myAttributes;

class RenderConfigEdit extends \Dcp\Ui\DefaultEdit
{
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return __METHOD__;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);


        $options->htmltext(myAttributes::tst_desc)->setToolbar(\Dcp\Ui\HtmltextRenderOptions::basicToolbar);


        return $options;
    }
}
