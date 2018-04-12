<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class AttributeModel_setValueEdition extends \Dcp\Ui\DefaultEdit
{
    public function getJsReferences(\Doc $document = null)
    {
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js = parent::getJsReferences();
        $js["tstAddbuttonJS"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_alltype/AttributeModel_setValueEdition.js?ws=" . $version;
        return $js;
    }

}
