<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Ui\HtmltextRenderOptions;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class HtmltextToolbarSimple extends \Anakeen\Ui\DefaultEdit
{


    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);


        $options->frame()->setCollapse(true);
        $options->frame(myAttributes::test_ddui_all__fr_text)->setCollapse(false);
        $options->htmltext()->setToolbar(HtmltextRenderOptions::simpleToolbar);


        return $options;

    }
}
