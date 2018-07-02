<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Fields\Tst_ddui_alltype as myAttributes;

class AllRenderNotification extends \Dcp\Ui\DefaultView
{

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $jsReferences = parent::getJsReferences($document);
        $jsReferences["tstNotification"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/testNotifications.js?ws=" . $version;
        return $jsReferences;
    }
}
