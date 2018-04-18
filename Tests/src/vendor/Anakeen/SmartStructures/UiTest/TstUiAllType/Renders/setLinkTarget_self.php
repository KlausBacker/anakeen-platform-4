<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class setLinkTarget_self extends AllRenderConfigView
{
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testAddButtonJS.js?ws=" . $version;
        return $js;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        $linkOptionAccount = new \Dcp\ui\htmlLinkOptions();
        $linkOptionAccount->target = "_self";
        $linkOptionAccount->title = "Mon test {{value}} {{displayValue}}";
        $linkOptionAccount->url = "api/v1/documents/{{value}}/views/!defaultEdition.html";

        $linkOptionImage = new \Dcp\ui\htmlLinkOptions();
        $linkOptionImage->target = "_self";
        $linkOptionImage->title = ' <h3><img src="{{thumbnail}}&size=100"/>{{displayValue}}</h3>';
        $linkOptionImage->url = "{{{url}}}&size=200";

        $linkOption = new \Dcp\ui\htmlLinkOptions();
        $linkOption->target = "_self";
        $linkOption->title = "Mon test {{value}} {{displayValue}}";
        $linkOption->url = "#action/my:myOptions";

        $options->account()->setLink($linkOptionAccount);
        $options->image()->setLink($linkOptionImage);
        $options->commonOption()->setLink($linkOption);

        return $options;

    }
}
