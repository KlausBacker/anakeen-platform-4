<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class setLinkTarget_blank extends AllRenderConfigView
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $linkOptionAccount = new \Dcp\ui\htmlLinkOptions();
        $linkOptionAccount->target = "_blank";
        $linkOptionAccount->title = "Mon test {{value}} {{displayValue}}";
        $linkOptionAccount->url = "api/v1/documents/{{value}}/views/!defaultEdition.html";

        $linkOptionImage = new \Dcp\ui\htmlLinkOptions();
        $linkOptionImage->target = "_blank";
        $linkOptionImage->title = ' <h3><img src="{{thumbnail}}&size=100"/>{{displayValue}}</h3>';
        $linkOptionImage->url = "{{{url}}}&size=200";

        $linkOption = new \Dcp\ui\htmlLinkOptions();
        $linkOption->target = "_blank";
        $linkOption->title = "Mon test {{value}} {{displayValue}}";
        $linkOption->url = "https://fr.wikipedia.org/wiki/ {{value}} ";

        $options->account()->setLink($linkOptionAccount);
        $options->image()->setLink($linkOptionImage);
        $options->commonOption()->setLink($linkOption);

        return $options;

    }
}
