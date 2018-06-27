<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class AllRenderCssColor extends AllRenderConfigEdit
{

    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue("WVERSION");
        $cssReferences = parent::getCssReferences($document);
        $cssReferences["tstNotification"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testColor.css?ws=" . $version;
        return $cssReferences;
    }
}
