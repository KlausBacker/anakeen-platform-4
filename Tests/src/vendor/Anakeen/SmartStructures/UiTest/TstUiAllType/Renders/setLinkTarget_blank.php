<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class setLinkTarget_blank extends AllRenderConfigView
{

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $linkOptionAccount = new \Anakeen\Ui\htmlLinkOptions();
        $linkOptionAccount->target = "_blank";
        $linkOptionAccount->title = "Mon test {{value}} {{displayValue}}";
        $linkOptionAccount->url = "/api/v2/smart-elements/{{value}}/views/!defaultEdition.html";

        $linkOptionImage = new \Anakeen\Ui\htmlLinkOptions();
        $linkOptionImage->target = "_blank";
        $linkOptionImage->title = ' <h3><img src="{{thumbnail}}&size=100"/>{{displayValue}}</h3>';
        $linkOptionImage->url = "{{{url}}}&size=200";

        $linkOption = new \Anakeen\Ui\htmlLinkOptions();
        $linkOption->target = "_blank";
        $linkOption->title = "Mon test {{value}} {{displayValue}}";
        $linkOption->url = "https://fr.wikipedia.org/wiki/ {{value}} ";

        $options->account()->setLink($linkOptionAccount);
        $options->image()->setLink($linkOptionImage);
        $options->commonOption()->setLink($linkOption);

        return $options;

    }
}
