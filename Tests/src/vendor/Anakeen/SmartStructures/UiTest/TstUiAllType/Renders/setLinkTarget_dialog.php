<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class setLinkTarget_dialog extends AllRenderConfigView
{

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        $linkOptionAccount = new \Dcp\ui\htmlLinkOptions();
        $linkOptionAccount->target = "_dialog";
        $linkOptionAccount->title = "Mon test {{value}} {{displayValue}}";
        $linkOptionAccount->windowHeight = "300px";
        $linkOptionAccount->windowWidth = "500px";
        $linkOptionAccount->windowTitle = "Mon test {{value}} {{displayValue}}";
        $linkOptionAccount->url = "api/v1/documents/{{value}}/views/!defaultEdition.html";

        $linkOptionImage = new \Dcp\ui\htmlLinkOptions();
        $linkOptionImage->target = "_dialog";
        $linkOptionImage->title = ' <h3><img src="{{thumbnail}}&size=100"/>{{displayValue}}</h3>';
        $linkOptionImage->windowHeight = "300px";
        $linkOptionImage->windowWidth = "500px";
        $linkOptionImage->windowTitle = "Mon test {{value}} {{displayValue}}";
        $linkOptionImage->url = "{{{url}}}&size=200";

        $linkOption = new \Dcp\ui\htmlLinkOptions();
        $linkOption->target = "_dialog";
        $linkOption->title = "Mon test {{value}} {{displayValue}}";
        $linkOption->windowHeight = "300px";
        $linkOption->windowWidth = "500px";
        $linkOption->windowTitle = "Mon test {{value}} {{displayValue}}";
        $linkOption->url = "https://fr.wikipedia.org/wiki/{{value}}";

        $options->account()->setLink($linkOptionAccount);
        $options->image()->setLink($linkOptionImage);
        $options->commonOption()->setLink($linkOption);

        return $options;

    }
}
