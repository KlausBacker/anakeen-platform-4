<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class AllRenderNotification extends \Dcp\Ui\DefaultView
{

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue("WVERSION");
        $jsReferences = parent::getJsReferences($document);
        $jsReferences["tstNotification"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testNotifications.js?ws=" . $version;
        return $jsReferences;
    }
}
